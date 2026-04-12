<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\IssueApiTokenAction;
use App\Http\Controllers\Api\Concerns\RespondsWithApiJson;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticatedSessionController extends Controller
{
    use RespondsWithApiJson;

    public function store(
        LoginRequest $request,
        AuthenticateUserAction $authenticateUserAction,
        IssueApiTokenAction $issueApiTokenAction,
    ): JsonResponse {
        $payload = $request->validated();
        $deviceName = $this->resolveDeviceName($payload);

        $user = $authenticateUserAction->handle(
            email: $payload['email'],
            password: $payload['password'],
        );

        if (! $user instanceof User) {
            return $this->error(
                request: $request,
                message: trans('auth.failed'),
                errors: [
                    'email' => [trans('auth.failed')],
                ],
                status: 401,
            );
        }

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $issueApiTokenAction->handle($user, $deviceName);

        return $this->success(
            request: $request,
            message: 'Login successful.',
            data: [
                'user' => new AuthUserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        );
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->error(
                request: $request,
                message: 'Unauthorized.',
                status: 401,
            );
        }

        $accessToken = $user->currentAccessToken();

        if ($accessToken instanceof PersonalAccessToken) {
            $accessToken->delete();
        }

        return $this->success(
            request: $request,
            message: 'Logout successful.',
            data: [
                'revoked' => true,
            ],
        );
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->error(
                request: $request,
                message: 'Unauthorized.',
                status: 401,
            );
        }

        $user->tokens()->delete();

        return $this->success(
            request: $request,
            message: 'All tokens revoked successfully.',
            data: [
                'revoked_all' => true,
            ],
        );
    }

    private function resolveDeviceName(array $payload): string
    {
        $deviceName = trim((string) ($payload['device_name'] ?? ''));

        if ($deviceName === '') {
            return 'default-device';
        }

        return $deviceName;
    }
}
