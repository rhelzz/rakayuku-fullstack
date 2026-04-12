<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Concerns\RespondsWithApiJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    use RespondsWithApiJson;

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return $this->error(
                request: $request,
                message: 'Unauthorized.',
                status: 401,
            );
        }

        return $this->success(
            request: $request,
            message: 'Authenticated user retrieved successfully.',
            data: [
                'user' => new AuthUserResource($user),
            ],
        );
    }
}
