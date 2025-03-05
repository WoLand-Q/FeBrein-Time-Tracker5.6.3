<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    public function index(): JsonResource
    {
        $users = User::all();

        return UserResource::collection($users);
    }


    public function update(User $user, UserStoreRequest $request): JsonResource
    {
        /** @var UserService $service */
        $service = resolve(UserService::class);

        $updatedUser = $service->update($user, $request->validated()); // Используем только валидированные данные

        return UserResource::make($updatedUser);
    }


    public function destroy(User $user): bool
    {
        /** @var UserService $service */
        $service = resolve(UserService::class);

        return $service->destroy($user);
    }

    public function show(User $user): JsonResource
    {
       // $user->load('creator'); якийсь прикол которий

        return UserResource::make($user);
    }

    public function store(UserStoreRequest $request): JsonResource
    {
        /** @var UserService $service */
        $service = resolve(UserService::class);

        // Получаем только валидированные данные
        $data = $request->validated();

        // Сохраняем группу через сервис
        $user = $service->store($data);

        // Возвращаем ресурс
        return UserResource::make($user);
    }


}


