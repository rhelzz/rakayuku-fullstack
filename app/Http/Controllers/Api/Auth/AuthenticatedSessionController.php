<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\Actions\Auth\IssueApiTokenAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthenticatedSessionController extends Controller
{
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
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $issueApiTokenAction->handle($user, $deviceName);

        return response()->json([
            'message' => 'Login successful.',
            'user' => new AuthUserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $accessToken = $user->currentAccessToken();

        if ($accessToken instanceof PersonalAccessToken) {
            $accessToken->delete();
        }

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $user->tokens()->delete();

        return response()->json([
            'message' => 'All tokens revoked successfully.',
        ]);
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
