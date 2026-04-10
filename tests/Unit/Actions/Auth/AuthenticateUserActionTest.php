<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticateUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_user_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'valid@example.com',
            'password' => Hash::make('ValidPass!123'),
            'role' => UserRole::Hr,
        ]);

        $action = app(AuthenticateUserAction::class);

        $result = $action->handle('valid@example.com', 'ValidPass!123');

        $this->assertInstanceOf(User::class, $result);
        $this->assertTrue($result->is($user));
    }

    public function test_it_returns_null_when_password_is_invalid(): void
    {
        User::factory()->create([
            'email' => 'invalid-pass@example.com',
            'password' => Hash::make('ValidPass!123'),
            'role' => UserRole::Hr,
        ]);

        $action = app(AuthenticateUserAction::class);

        $result = $action->handle('invalid-pass@example.com', 'WrongPass!123');

        $this->assertNull($result);
    }

    public function test_it_returns_null_for_unknown_email(): void
    {
        $action = app(AuthenticateUserAction::class);

        $result = $action->handle('missing@example.com', 'WhateverPass!123');

        $this->assertNull($result);
    }
}
