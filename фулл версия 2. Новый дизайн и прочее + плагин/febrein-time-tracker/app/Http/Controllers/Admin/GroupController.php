<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupStoreRequest;
use App\Http\Resources\Admin\GroupResource;
use App\Models\Group;
use App\Services\Admin\GroupService;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupController extends Controller
{
    public function index(): JsonResource
    {
        $groups = Group::all();
        return GroupResource::collection($groups);
    }


    public function update(Group $group, GroupStoreRequest $request): JsonResource
    {
        /** @var GroupService $service */
        $service = resolve(GroupService::class);

        $updatedGroup = $service->update($group, $request->validated()); // Используем только валидированные данные

        return GroupResource::make($updatedGroup);
    }


    public function destroy(Group $group): bool
    {
        /** @var GroupService $service */
        $service = resolve(GroupService::class);

        return $service->destroy($group);
    }

    public function show(Group $group): JsonResource
    {
       // $group->load('creator'); якийсь прикол которий

        return GroupResource::make($group);
    }

    public function store(GroupStoreRequest $request): JsonResource
    {
        /** @var GroupService $service */
        $service = resolve(GroupService::class);

        // Получаем только валидированные данные
        $data = $request->validated();

        // Сохраняем группу через сервис
        $group = $service->store($data);

        // Возвращаем ресурс
        return GroupResource::make($group);
    }


}


