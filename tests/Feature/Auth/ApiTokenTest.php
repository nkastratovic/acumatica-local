<?php

namespace Tests\Feature\Auth;

use App\Enums\Ability;
use App\Models\Role;
use App\Models\User;
use App\Services\Acumatica\SalesOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private const URL = '/api/acumatica/sales-orders/SO/SO1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(SalesOrderService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSalesOrder')->with('SO', 'SO1')->andReturn(['OrderNbr' => ['value' => 'SO1']]);
        });
    }

    private function tokenFor(User $user, array $abilities = ['sales-orders.view'], $expiresAt = null): string
    {
        return $user->createToken('test', $abilities, $expiresAt)->plainTextToken;
    }

    public function test_requests_without_a_token_get_401_json(): void
    {
        $this->getJson(self::URL)->assertUnauthorized();
        $this->get(self::URL)->assertUnauthorized()->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_valid_token_with_ability_can_fetch_sales_order(): void
    {
        $user = User::factory()->withRole(Role::MANAGER)->create();

        $this->withToken($this->tokenFor($user))->getJson(self::URL)
            ->assertOk()
            ->assertJsonPath('OrderNbr.value', 'SO1');
    }

    public function test_api_user_endpoint_describes_the_caller(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();

        $this->withToken($this->tokenFor($user))->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('roles', ['viewer'])
            ->assertJsonPath('token_abilities', ['sales-orders.view']);
    }

    public function test_token_without_the_ability_is_forbidden(): void
    {
        $user = User::factory()->withRole(Role::ADMIN)->create();

        $this->withToken($this->tokenFor($user, []))->getJson(self::URL)->assertForbidden();
    }

    public function test_token_is_forbidden_once_the_user_loses_the_role(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();
        $token = $this->tokenFor($user);

        $user->roles()->detach();

        $this->withToken($token)->getJson(self::URL)->assertForbidden();
    }

    public function test_inactive_users_tokens_are_rejected(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();
        $token = $this->tokenFor($user);
        $user->update(['is_active' => false]);

        $this->withToken($token)->getJson(self::URL)->assertForbidden();
    }

    public function test_expired_tokens_are_rejected(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();

        $this->withToken($this->tokenFor($user, ['sales-orders.view'], now()->subMinute()))
            ->getJson(self::URL)->assertUnauthorized();
    }

    public function test_manager_can_create_and_revoke_a_token_in_the_ui(): void
    {
        $user = User::factory()->withRole(Role::MANAGER)->create();

        $this->actingAs($user)->post('/account/tokens', [
            'name' => 'Warehouse sync',
            'abilities' => ['sales-orders.view'],
            'expires' => '360',
        ])->assertRedirect(route('account.tokens.index'))->assertSessionHas('plainTextToken');

        $token = $user->tokens()->first();
        $this->assertSame(['sales-orders.view'], $token->abilities);
        $this->assertTrue($token->expires_at->between(now()->addHours(6)->subMinute(), now()->addHours(6)->addMinute()));

        $this->delete("/account/tokens/{$token->id}")->assertRedirect();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_tokens_cannot_carry_abilities_beyond_the_allowed_set(): void
    {
        $user = User::factory()->withRole(Role::ADMIN)->create();

        $this->actingAs($user)->post('/account/tokens', [
            'name' => 'Too much',
            'abilities' => [Ability::UsersManage->value],
            'expires' => '360',
        ])->assertSessionHasErrors('abilities.0');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_users_cannot_revoke_someone_elses_token(): void
    {
        $owner = User::factory()->withRole(Role::MANAGER)->create();
        $owner->createToken('x', ['sales-orders.view']);
        $other = User::factory()->withRole(Role::MANAGER)->create();

        $this->actingAs($other)->delete('/account/tokens/'.$owner->tokens()->first()->id)->assertNotFound();
        $this->assertSame(1, $owner->tokens()->count());
    }

    public function test_user_with_temporary_password_cannot_use_api(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create(['must_change_password' => true]);

        $this->withToken($this->tokenFor($user))->getJson(self::URL)->assertForbidden();
    }
}
