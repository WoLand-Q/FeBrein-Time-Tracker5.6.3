<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserWorkDayStoreRequest;
use App\Http\Resources\Admin\UserWorkDayResource;
use App\Models\UserWorkDay;
use App\Services\Admin\UserWorkDayService;
use Illuminate\Http\Resources\Json\JsonResource;

class UserWorkDayController extends Controller
{
    public function index(): JsonResource
    {
        $userWorkDay = UserWorkDay::all();

        return UserWorkDayResource::collection($userWorkDay);
    }


    public function update(UserWorkDay $userWorkDay, UserWorkDayStoreRequest $request): JsonResource
    {
        //dd($userWorkDay);
        /** @var UserWorkDayService $service */
        $service = resolve(UserWorkDayService::class);

        $updatedUserWorkDay = $service->update($userWorkDay, $request->validated()); // Используем только валидированные данные

        return UserWorkDayResource::make($updatedUserWorkDay);
    }


    public function destroy(UserWorkDay $userWorkDay): bool
    {
        //dd($userWorkDay);
        /** @var UserWorkDayService $service */
        $service = resolve(UserWorkDayService::class);

        return $service->destroy($userWorkDay);
    }

    public function show(UserWorkDay $userWorkDay): JsonResource
    {
        // $userWorkDay->load('creator'); якийсь прикол которий
        return UserWorkDayResource::make($userWorkDay);
    }

    public function store(UserWorkDayStoreRequest $request): JsonResource
    {
        /** @var UserWorkDayService $service */
        $service = resolve(UserWorkDayService::class);

        // Получаем только валидированные данные
        $data = $request->validated();
        try {
            $userWorkDay = $service->store($data);
        } catch (\Throwable $exception) {
            return JsonResource::make(['error' => $exception->getMessage()]);
        };


        // Возвращаем ресурс
        return UserWorkDayResource::make($userWorkDay);
    }


}


