<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\AuthUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class CurrentUserController extends Controller
{
    public function __invoke(Request $request): AuthUserResource
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return new AuthUserResource($user);
    }
}
