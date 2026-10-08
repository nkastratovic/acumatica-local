# Authentication & authorization

## Overview

| Layer | How |
|---|---|
| Browser login | Session guard (`web`), `/login`, rate-limited to 5 attempts/min per email+IP |
| API | Laravel Sanctum personal access tokens, `Authorization: Bearer <token>` on `/api/*` |
| Authorization | Roles → permissions stored in DB, one Gate per permission (`App\Enums\Ability`) |
| Accounts | No self-registration. Admins create users; first admin via artisan |

## First-time setup (Sail)

```bash
./vendor/bin/sail composer install
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed --class=RolesAndPermissionsSeeder
./vendor/bin/sail artisan app:create-user you@company.com --name="Your Name" --role=admin
```

Re-run the seeder on every deploy; it is idempotent and keeps role assignments made in the UI.

## Roles (defaults)

| Role | Permissions |
|---|---|
| `admin` | everything (implicit, can't be changed or deleted) |
| `manager` | `sales-orders.view`, `api-tokens.manage` |
| `viewer` | `sales-orders.view` |

Admins can create more roles and change permissions at **/admin/roles**.

## Permissions

| Permission | Grants |
|---|---|
| `sales-orders.view` | Sales order pages and `GET /api/acumatica/sales-orders/{type}/{nbr}` |
| `api-tokens.manage` | Issue / revoke own API tokens at **/account/tokens** |
| `users.manage` | **/admin/users**: create, edit, disable users, reset passwords, revoke tokens |
| `roles.manage` | **/admin/roles** |

### Adding a new permission

1. Add a case to `app/Enums/Ability.php` (plus `label()` / `description()`; add it to `tokenAbilities()` if it should be usable via the API).
2. `php artisan db:seed --class=RolesAndPermissionsSeeder`
3. Protect routes with `->middleware('can:your.permission')`, or in code `Gate::authorize(Ability::YourCase->value)`, or in Blade `@can('your.permission')`.
4. Assign it to roles in **/admin/roles**.

## API tokens

* A token carries a subset of abilities chosen at creation and an expiry (30/90/365 days or never).
* A request succeeds only if the user's **roles** grant the permission **and** the **token** has the ability. Removing a role takes effect immediately for existing tokens.
* Disabling a user deletes all of their tokens; a disabled user's browser session ends on their next request.
* Expired tokens are pruned daily (`sanctum:prune-expired`; needs the scheduler running, e.g. `php artisan schedule:work`).
* The API is token-only: browser session cookies are not accepted on `/api/*`.

```bash
curl -H "Authorization: Bearer 1|abc..." -H "Accept: application/json" \
     http://localhost:8080/api/acumatica/sales-orders/SO/SO005483

# Who am I: roles, permissions, token abilities
curl -H "Authorization: Bearer 1|abc..." -H "Accept: application/json" \
     http://localhost:8080/api/user
```

## Account safety rules

* Users created by an admin, and passwords reset by an admin, are temporary: the user must change it before doing anything else (web and API).
* Passwords: min 12 chars, mixed case and numbers; in production also checked against known breaches (haveibeenpwned).
* Changing a password signs the user out of their other browser sessions.
* An admin can't disable or demote themselves, and the last active admin can't be demoted.
