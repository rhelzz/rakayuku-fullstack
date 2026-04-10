<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticateUserAction
{
    public function handle(string $email, string $password): ?User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            return null;
        }

        if (! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
