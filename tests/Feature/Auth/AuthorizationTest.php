<?php

namespace Tests\Feature\Auth;

use App\Enums\Ability;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Acumatica\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(SalesOrderService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSalesOrder')->andReturn(['OrderNbr' => ['value' => 'SO1']]);
        });
    }

    public function test_viewer_can_look_up_sales_orders(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();

        $this->actingAs($user)->get('/')->assertRedirect(route('acumatica.sales-orders.create'));
        $this->get('/acumatica/sales-orders')->assertOk();
        $this->get('/acumatica/sales-orders/SO/SO1')->assertOk()->assertSee('SO1');
    }

    public function test_user_without_roles_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')->assertOk()->assertSee('no access');
        $this->get('/acumatica/sales-orders')->assertForbidden();
        $this->get('/acumatica/sales-orders/SO/SO1')->assertForbidden();
    }

    public function test_viewer_cannot_reach_admin_or_tokens(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();

        $this->actingAs($user);
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/roles')->assertForbidden();
        $this->get('/account/tokens')->assertForbidden();
    }

    public function test_manager_can_manage_tokens_but_not_users(): void
    {
        $user = User::factory()->withRole(Role::MANAGER)->create();

        $this->actingAs($user);
        $this->get('/account/tokens')->assertOk();
        $this->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_reach_everything(): void
    {
        $user = User::factory()->withRole(Role::ADMIN)->create();

        $this->actingAs($user);
        $this->get('/acumatica/sales-orders')->assertOk();
        $this->get('/account/tokens')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/roles')->assertOk();
    }

    public function test_permissions_granted_to_a_custom_role_take_effect(): void
    {
        $role = Role::create(['name' => 'auditor', 'label' => 'Auditor']);
        $role->permissions()->attach(Permission::where('name', Ability::UsersManage->value)->first());
        $user = User::factory()->withRole('auditor')->create();

        $this->actingAs($user);
        $this->get('/admin/users')->assertOk();
        $this->get('/acumatica/sales-orders')->assertForbidden();
    }

    public function test_seeder_is_idempotent_and_keeps_custom_assignments(): void
    {
        $viewer = Role::where('name', Role::VIEWER)->first();
        $viewer->permissions()->sync([]);

        $this->seed();

        $this->assertSame(count(Ability::cases()), Permission::count());
        $this->assertSame(0, $viewer->permissions()->count());
    }
}
