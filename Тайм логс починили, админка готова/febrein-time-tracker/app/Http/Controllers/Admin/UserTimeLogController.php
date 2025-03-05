<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserTimeLogStoreRequest;
use App\Http\Resources\Admin\UserTimeLogResource;
use App\Models\UserTimeLog;
use App\Services\Admin\UserTimeLogService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTimeLogController extends Controller
{
    public function index(): JsonResource
    {
        $userTimeLog = UserTimeLog::all();

        return UserTimeLogResource::collection($userTimeLog);
    }


    public function update(UserTimeLog $userTimeLog, UserTimeLogStoreRequest $request): JsonResource
    {
        //dd($userTimeLog);
        /** @var UserTimeLogService $service */
        $service = resolve(UserTimeLogService::class);

        $updatedUserTimeLog = $service->update($userTimeLog, $request->validated()); // Используем только валидированные данные

        return UserTimeLogResource::make($updatedUserTimeLog);
    }


    public function destroy(UserTimeLog $userTimeLog): bool
    {
        //dd($userTimeLog);
        /** @var UserTimeLogService $service */
        $service = resolve(UserTimeLogService::class);

        return $service->destroy($userTimeLog);
    }

    public function show(UserTimeLog $userTimeLog): JsonResource
    {
        // $userTimeLog->load('creator'); якийсь прикол которий
        return UserTimeLogResource::make($userTimeLog);
    }

    public function store(UserTimeLogStoreRequest $request): JsonResource
    {
        /** @var UserTimeLogService $service */
        $service = resolve(UserTimeLogService::class);

        // Получаем только валидированные данные
        $data = $request->validated();

        try {
            $userTimeLog = $service->store($data);
        } catch (\Throwable $exception) {
            return JsonResource::make(['error' => $exception->getMessage()]);
        };


        // Возвращаем ресурс
        return UserTimeLogResource::make($userTimeLog);
    }


}


