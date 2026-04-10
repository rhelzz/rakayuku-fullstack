<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\IssueApiTokenAction;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class IssueApiTokenActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_role_scoped_token_for_finance_user(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Finance,
        ]);

        $action = app(IssueApiTokenAction::class);

        $plainTextToken = $action->handle($user, 'unit-device');

        $this->assertStringContainsString('|', $plainTextToken);

        [$tokenId] = explode('|', $plainTextToken, 2);

        $token = PersonalAccessToken::query()->find((int) $tokenId);

        $this->assertInstanceOf(PersonalAccessToken::class, $token);
        $this->assertContains('profile:read', $token->abilities);
        $this->assertContains('finance:read', $token->abilities);
        $this->assertContains('finance:write', $token->abilities);
        $this->assertNotContains('*', $token->abilities);
        $this->assertNotNull($token->expires_at);
    }

    public function test_it_issues_full_access_token_for_admin_user(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $action = app(IssueApiTokenAction::class);

        $plainTextToken = $action->handle($user, 'admin-device');

        [$tokenId] = explode('|', $plainTextToken, 2);

        $token = PersonalAccessToken::query()->find((int) $tokenId);

        $this->assertInstanceOf(PersonalAccessToken::class, $token);
        $this->assertSame(['*'], $token->abilities);
    }
}
