<?php

namespace App\Http\Resources\Admin;
use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Carbon acted_at */
class UserTimeLogResource extends JsonResource
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
            'acted_at' => $this->acted_at->toDateTimeString(),//такж є ще метод format для якого можна вказати маску
            'event_id' => $this->event_id,
            'even_name' => $this->event_id->label()
        ];
    }
}
