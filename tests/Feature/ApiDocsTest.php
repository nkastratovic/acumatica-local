<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/docs/api')->assertRedirect(route('login'));
        $this->get('/docs/api.json')->assertRedirect(route('login'));
    }

    public function test_non_admin_holding_every_permission_gets_403(): void
    {
        $role = Role::create(['name' => 'power-user', 'label' => 'Power user']);
        $role->permissions()->sync(Permission::pluck('id'));
        $user = User::factory()->create();
        $user->roles()->attach($role);

        $this->actingAs($user);
        $this->get('/docs/api')->assertForbidden();
        $this->get('/docs/api.json')->assertForbidden();
    }

    public function test_admin_sees_every_api_endpoint_with_bearer_auth(): void
    {
        $admin = User::factory()->withRole(Role::ADMIN)->create();

        $this->actingAs($admin);
        $this->get('/docs/api')->assertOk()->assertSee('Acumatica Local API');
        $this->get('/docs/api.json')
            ->assertOk()
            ->assertJsonPath('components.securitySchemes.http.scheme', 'bearer')
            ->assertJsonStructure(['paths' => [
                '/user',
                '/acumatica/sales-orders/{orderType}/{orderNbr}',
            ]]);
    }

    public function test_admin_with_temporary_password_is_sent_to_change_it(): void
    {
        $admin = User::factory()->withRole(Role::ADMIN)->create(['must_change_password' => true]);

        $this->actingAs($admin)->get('/docs/api')->assertRedirect(route('account.password.edit'));
    }
}
