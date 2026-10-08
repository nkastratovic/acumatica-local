<?php

namespace Tests\Feature\Auth;

use App\Enums\Ability;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAndRoleAdminTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->withRole(Role::ADMIN)->create();
    }

    private function roleId(string $name): int
    {
        return Role::where('name', $name)->value('id');
    }

    public function test_admin_creates_user_with_temporary_password(): void
    {
        $this->actingAs($this->admin)->post('/admin/users', [
            'name' => 'Jana',
            'email' => 'jana@example.com',
            'password' => 'TempPassword123',
            'password_confirmation' => 'TempPassword123',
            'roles' => [$this->roleId(Role::VIEWER)],
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'jana@example.com')->first();
        $this->assertTrue($user->must_change_password);
        $this->assertTrue($user->hasRole(Role::VIEWER));
    }

    public function test_deactivating_a_user_revokes_their_tokens(): void
    {
        $user = User::factory()->withRole(Role::MANAGER)->create();
        $user->createToken('x', ['sales-orders.view']);

        $this->actingAs($this->admin)->put("/admin/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => '0',
            'roles' => [$this->roleId(Role::MANAGER)],
        ])->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_admin_cannot_deactivate_or_demote_self(): void
    {
        $this->actingAs($this->admin)->put("/admin/users/{$this->admin->id}", [
            'name' => 'x', 'email' => $this->admin->email, 'is_active' => '0', 'roles' => [$this->roleId(Role::ADMIN)],
        ])->assertSessionHasErrors('is_active');

        $this->put("/admin/users/{$this->admin->id}", [
            'name' => 'x', 'email' => $this->admin->email, 'is_active' => '1', 'roles' => [],
        ])->assertSessionHasErrors('roles');

        $this->assertTrue($this->admin->fresh()->isAdmin());
    }

    public function test_last_active_admin_cannot_be_demoted_by_a_user_manager(): void
    {
        $role = Role::create(['name' => 'hr', 'label' => 'HR']);
        $role->permissions()->attach(Permission::where('name', Ability::UsersManage->value)->first());
        $hr = User::factory()->withRole('hr')->create();

        $this->actingAs($hr)->put("/admin/users/{$this->admin->id}", [
            'name' => 'x', 'email' => $this->admin->email, 'is_active' => '1', 'roles' => [],
        ])->assertSessionHasErrors('roles');
    }

    public function test_reset_password_forces_change(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();

        $this->actingAs($this->admin)->put("/admin/users/{$user->id}", [
            'name' => $user->name, 'email' => $user->email, 'is_active' => '1',
            'password' => 'ResetPassword123', 'password_confirmation' => 'ResetPassword123',
            'roles' => [$this->roleId(Role::VIEWER)],
        ])->assertRedirect();

        $this->assertTrue($user->fresh()->must_change_password);
    }

    public function test_admin_revokes_all_tokens_of_a_user(): void
    {
        $user = User::factory()->withRole(Role::MANAGER)->create();
        $user->createToken('a', ['sales-orders.view']);
        $user->createToken('b', ['sales-orders.view']);

        $this->actingAs($this->admin)->delete("/admin/users/{$user->id}/tokens")->assertRedirect();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_admin_creates_and_edits_a_role(): void
    {
        $view = Permission::where('name', Ability::SalesOrdersView->value)->value('id');

        $this->actingAs($this->admin)->post('/admin/roles', [
            'name' => 'warehouse', 'label' => 'Warehouse', 'permissions' => [$view],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::where('name', 'warehouse')->first();
        $this->assertEquals([$view], $role->permissions->pluck('id')->all());

        $this->put("/admin/roles/{$role->id}", ['name' => 'warehouse', 'label' => 'Warehouse team', 'permissions' => []])
            ->assertRedirect();
        $this->assertSame(0, $role->permissions()->count());

        $this->delete("/admin/roles/{$role->id}")->assertRedirect();
        $this->assertNull(Role::find($role->id));
    }

    public function test_admin_role_cannot_be_deleted_or_renamed(): void
    {
        $id = $this->roleId(Role::ADMIN);

        $this->actingAs($this->admin)->delete("/admin/roles/{$id}")->assertForbidden();
        $this->put("/admin/roles/{$id}", ['name' => 'boss', 'label' => 'Boss'])->assertSessionHasErrors('name');
        $this->put("/admin/roles/{$id}", ['label' => 'Super admin'])->assertRedirect();

        $this->assertSame('admin', Role::find($id)->name);
    }

    public function test_admin_pages_render(): void
    {
        $this->actingAs($this->admin);
        $this->get('/admin/users')->assertOk()->assertSee($this->admin->email);
        $this->get('/admin/users/create')->assertOk();
        $this->get("/admin/users/{$this->admin->id}/edit")->assertOk();
        $this->get('/admin/roles')->assertOk()->assertSee('all permissions');
        $this->get('/admin/roles/create')->assertOk();
        $this->get('/admin/roles/'.$this->roleId(Role::VIEWER).'/edit')->assertOk();
        $this->get('/account/tokens')->assertOk();
        $this->get('/account/password')->assertOk();
    }
}
