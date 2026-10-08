<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/acumatica/sales-orders')->assertRedirect(route('login'));
        $this->get('/admin/users')->assertRedirect(route('login'));
    }

    public function test_login_screen_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_users_can_sign_in_and_are_sent_home(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_sign_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_users_deactivated_mid_session_are_signed_out(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create();
        $this->actingAs($user);

        $user->update(['is_active' => false]);

        $this->get('/acumatica/sales-orders')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_temporary_password_must_be_changed_first(): void
    {
        $user = User::factory()->withRole(Role::VIEWER)->create(['must_change_password' => true]);

        $this->actingAs($user)->get('/acumatica/sales-orders')
            ->assertRedirect(route('account.password.edit'));

        $this->put('/account/password', [
            'current_password' => 'password',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect(route('home'));

        $this->assertFalse($user->fresh()->must_change_password);
        $this->get('/acumatica/sales-orders')->assertOk();
    }

    public function test_password_change_requires_current_password_and_strong_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'not-it',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors(['current_password', 'password']);
    }
}
