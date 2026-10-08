<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_it_creates_an_admin(): void
    {
        $this->artisan('app:create-user', [
            'email' => 'boss@example.com', '--name' => 'Boss', '--password' => 'StrongPassword1',
        ])->assertSuccessful();

        $this->assertTrue(User::where('email', 'boss@example.com')->first()->isAdmin());
    }

    public function test_it_rejects_weak_passwords_and_unknown_roles(): void
    {
        $this->artisan('app:create-user', ['email' => 'a@example.com', '--name' => 'A', '--password' => 'weak'])
            ->assertFailed();
        $this->artisan('app:create-user', ['email' => 'a@example.com', '--name' => 'A', '--password' => 'StrongPassword1', '--role' => 'nope'])
            ->assertFailed();

        $this->assertSame(0, User::count());
    }
}
