<?php

namespace Database\Seeders;

use App\Enums\Ability;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Idempotent: safe to run on every deploy
 *   php artisan db:seed --class=RolesAndPermissionsSeeder --force
 *
 * Syncs the permissions table with the Ability enum and creates the
 * default roles. Existing role->permission assignments made in the admin
 * UI are kept; default permissions are only attached when a role is new.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** @var array<string, array{label: string, description: string, abilities: list<Ability>}> */
    private const DEFAULT_ROLES = [
        Role::ADMIN => [
            'label' => 'Administrator',
            'description' => 'Full access, including users and roles.',
            'abilities' => [],
        ],
        Role::MANAGER => [
            'label' => 'Manager',
            'description' => 'Views sales orders and can issue API tokens for integrations.',
            'abilities' => [Ability::SalesOrdersView, Ability::ApiTokensManage],
        ],
        Role::VIEWER => [
            'label' => 'Viewer',
            'description' => 'Read-only access to sales orders.',
            'abilities' => [Ability::SalesOrdersView],
        ],
    ];

    public function run(): void
    {
        foreach (Ability::cases() as $ability) {
            Permission::updateOrCreate(
                ['name' => $ability->value],
                ['label' => $ability->label(), 'description' => $ability->description()],
            );
        }

        Permission::whereNotIn('name', Ability::values())->delete();

        foreach (self::DEFAULT_ROLES as $name => $definition) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['label' => $definition['label'], 'description' => $definition['description']],
            );

            if ($role->wasRecentlyCreated && $definition['abilities'] !== []) {
                $role->permissions()->sync(
                    Permission::whereIn('name', array_map(fn (Ability $a) => $a->value, $definition['abilities']))->pluck('id')
                );
            }
        }
    }
}
