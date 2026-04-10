<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\CarbonImmutable;

class IssueApiTokenAction
{
    public function handle(User $user, string $deviceName): string
    {
        $token = $user->createToken(
            name: $deviceName,
            abilities: $this->resolveAbilities($user),
            expiresAt: $this->resolveExpiration(),
        );

        return $token->plainTextToken;
    }

    private function resolveAbilities(User $user): array
    {
        $role = $user->role;

        if (! $role instanceof UserRole) {
            try {
                $role = UserRole::from((string) $role);
            } catch (\ValueError) {
                $role = UserRole::Hr;
            }
        }

        return match ($role) {
            UserRole::Admin => ['*'],
            UserRole::Finance => ['profile:read', 'finance:read', 'finance:write'],
            UserRole::Warehouse => ['profile:read', 'inventory:read', 'inventory:write'],
            UserRole::Hr => ['profile:read', 'employee:read', 'employee:write'],
        };
    }

    private function resolveExpiration(): ?CarbonImmutable
    {
        $expirationMinutes = (int) config('sanctum.expiration', 0);

        if ($expirationMinutes <= 0) {
            return null;
        }

        return CarbonImmutable::now()->addMinutes($expirationMinutes);
    }
}
