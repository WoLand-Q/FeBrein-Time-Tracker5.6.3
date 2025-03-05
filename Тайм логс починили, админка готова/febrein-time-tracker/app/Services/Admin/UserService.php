<?php

namespace App\Services\Admin;

use App\Enums\RoleEnum;
use App\Models\User;

class UserService
{
    public function store(array $data): User
    {
        $user = new User();
        $user->fill($data);
        $user->save();

        return $user;
    }

    public function update(User $user, array $data): User
    {
        $user->fill($data);
        $user->save();

        return $user;
    }

    public function destroy(User $user): bool
    {
        return $user->delete();
    }
}
