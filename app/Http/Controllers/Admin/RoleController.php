<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::with('permissions')->withCount('users')->orderBy('label')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => new Role,
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $role = Role::create(Arr::only($validated, ['name', 'label', 'description']));
        $role->permissions()->sync($validated['permissions'] ?? []);

        Log::info('Role created', ['by' => $request->user()->id, 'role' => $role->name]);

        return redirect()->route('admin.roles.index')->with('status', "Role {$role->label} created.");
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role->load('permissions'),
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $this->validated($request, $role);

        // The admin role's name is referenced in code and it implicitly has every permission.
        if ($role->isAdmin()) {
            $role->update(['label' => $validated['label'], 'description' => $validated['description'] ?? null]);
        } else {
            $role->update(Arr::only($validated, ['name', 'label', 'description']));
            $role->permissions()->sync($validated['permissions'] ?? []);
        }

        Log::info('Role updated', ['by' => $request->user()->id, 'role' => $role->name]);

        return redirect()->route('admin.roles.index')->with('status', "Role {$role->label} updated.");
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->isAdmin(), 403, 'The administrator role cannot be deleted.');

        $role->delete();

        Log::info('Role deleted', ['by' => $request->user()->id, 'role' => $role->name]);

        return redirect()->route('admin.roles.index')->with('status', "Role {$role->label} deleted.");
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'name' => [
                Rule::requiredIf(! $role?->isAdmin()),
                'string', 'max:50', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('roles')->ignore($role),
                Rule::when($role?->isAdmin(), ['in:'.Role::ADMIN]),
            ],
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ]);
    }
}
