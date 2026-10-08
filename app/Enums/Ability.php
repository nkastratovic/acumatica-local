<?php

namespace App\Enums;

/**
 * Every permission the application knows about.
 *
 * The enum is the source of truth: each case becomes a Gate, a row in the
 * `permissions` table (via RolesAndPermissionsSeeder) and a selectable
 * ability for API tokens. Add a case here, re-run the seeder, then assign
 * it to roles from the admin UI.
 */
enum Ability: string
{
    case SalesOrdersView = 'sales-orders.view';
    case ApiTokensManage = 'api-tokens.manage';
    case UsersManage = 'users.manage';
    case RolesManage = 'roles.manage';

    public function label(): string
    {
        return match ($this) {
            self::SalesOrdersView => 'View sales orders',
            self::ApiTokensManage => 'Create and revoke own API tokens',
            self::UsersManage => 'Manage users',
            self::RolesManage => 'Manage roles and permissions',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SalesOrdersView => 'Look up sales orders in Acumatica (web and API).',
            self::ApiTokensManage => 'Issue personal API tokens for integrations.',
            self::UsersManage => 'Create, edit and deactivate users; assign roles; revoke tokens.',
            self::RolesManage => 'Create roles and change which permissions they grant.',
        };
    }

    /**
     * Abilities that make sense on an API token (subset of all abilities).
     *
     * @return list<self>
     */
    public static function tokenAbilities(): array
    {
        return [self::SalesOrdersView];
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(fn (self $a) => $a->value, self::cases());
    }
}
