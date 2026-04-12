<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('ValidPass!123'),
            'role' => UserRole::Finance,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'WrongPass!123',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', trans('auth.failed'))
            ->assertJsonPath('errors.email.0', trans('auth.failed'));

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_user_can_login_and_fetch_current_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('ValidPass!123'),
            'role' => UserRole::Finance,
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@example.com',
            'password' => 'ValidPass!123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.user.role', UserRole::Finance->value)
            ->assertJsonPath('data.token_type', 'Bearer');

        $token = (string) $loginResponse->json('data.token');

        $meResponse = $this->withToken($token)->getJson('/api/v1/auth/me');

        $meResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Authenticated user retrieved successfully.')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.user.role', UserRole::Finance->value);
    }

    public function test_login_is_rate_limited_after_multiple_failed_attempts(): void
    {
        User::factory()->create([
            'email' => 'limited@example.com',
            'password' => Hash::make('ValidPass!123'),
            'role' => UserRole::Warehouse,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'limited@example.com',
                'password' => 'WrongPass!123',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'limited@example.com',
            'password' => 'WrongPass!123',
        ])->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('errors.throttle.0', 'Rate limit exceeded.');
    }

    public function test_logout_revokes_only_current_token(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $tokenA = $user->createToken('device-a')->plainTextToken;
        $tokenB = $user->createToken('device-b')->plainTextToken;

        $this->withToken($tokenA)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.revoked', true);

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($tokenB)
            ->postJson('/api/v1/auth/logout-all')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.revoked_all', true);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
    }
}
