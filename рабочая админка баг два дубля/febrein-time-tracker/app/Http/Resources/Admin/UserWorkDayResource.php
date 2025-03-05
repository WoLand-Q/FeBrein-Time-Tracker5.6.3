<?php

namespace App\Http\Resources\Admin;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Carbon work_started_at
 * @property Carbon break_started_at
 * @property Carbon break_ended_at
 * @property Carbon work_ended_at
 */
class UserWorkDayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //dd ($this);

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'work_started_at' => $this->work_started_at->toDateTimeString(),//такж є ще метод format для якого можна вказати маску
            'break_started_at' => $this->break_started_at->toDateTimeString(),//такж є ще метод format для якого можна вказати маску
            'break_ended_at' => $this->break_ended_at->toDateTimeString(),//такж є ще метод format для якого можна вказати маску
            'work_ended_at' => $this->work_ended_at->toDateTimeString(),//такж є ще метод format для якого можна вказати маску
            'break_minutes' => $this->break_minutes,
            'work_minutes' => $this->work_minutes
        ];
    }
}
