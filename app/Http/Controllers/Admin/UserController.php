<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->withCount('tokens')->orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::orderBy('label')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'is_active' => true,
                'must_change_password' => true,
            ]);
            $user->roles()->sync($validated['roles'] ?? []);

            return $user;
        });

        Log::info('User created', ['by' => $request->user()->id, 'user_id' => $user->id]);

        return redirect()->route('admin.users.index')
            ->with('status', "User {$user->email} created. They must change the temporary password on first sign-in.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user->load('roles'),
            'roles' => Role::orderBy('label')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
            'roles' => ['array'],
            'roles.*' => ['integer', Rule::exists('roles', 'id')],
        ]);

        $roleIds = array_map('intval', $validated['roles'] ?? []);
        $isActive = $request->boolean('is_active');

        $this->guardAgainstLockout($request->user(), $user, $roleIds, $isActive);

        DB::transaction(function () use ($user, $validated, $roleIds, $isActive) {
            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'is_active' => $isActive,
            ]);

            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
                $user->must_change_password = true;
            }

            $user->save();
            $user->roles()->sync($roleIds);

            // A disabled user should not keep working API tokens.
            if (! $isActive) {
                $user->tokens()->delete();
            }
        });

        Log::info('User updated', ['by' => $request->user()->id, 'user_id' => $user->id, 'active' => $isActive, 'roles' => $roleIds]);

        return redirect()->route('admin.users.index')->with('status', "User {$user->email} updated.");
    }

    public function revokeTokens(Request $request, User $user): RedirectResponse
    {
        $count = $user->tokens()->delete();

        Log::info('User tokens revoked', ['by' => $request->user()->id, 'user_id' => $user->id, 'count' => $count]);

        return back()->with('status', "Revoked {$count} API token(s) for {$user->email}.");
    }

    /**
     * Prevent admins from locking themselves (or everyone) out.
     *
     * @param  list<int>  $roleIds
     */
    private function guardAgainstLockout(User $actor, User $target, array $roleIds, bool $isActive): void
    {
        $adminRoleId = Role::where('name', Role::ADMIN)->value('id');
        $willBeAdmin = in_array($adminRoleId, $roleIds, true) && $isActive;

        if ($actor->is($target) && ! $isActive) {
            throw ValidationException::withMessages(['is_active' => 'You cannot deactivate your own account.']);
        }

        if ($actor->is($target) && $actor->isAdmin() && ! $willBeAdmin) {
            throw ValidationException::withMessages(['roles' => 'You cannot remove the administrator role from yourself.']);
        }

        if ($target->isAdmin() && ! $willBeAdmin) {
            $otherActiveAdmins = User::active()
                ->whereKeyNot($target->id)
                ->whereHas('roles', fn ($q) => $q->where('name', Role::ADMIN))
                ->exists();

            if (! $otherActiveAdmins) {
                throw ValidationException::withMessages(['roles' => 'At least one active administrator is required.']);
            }
        }
    }
}
