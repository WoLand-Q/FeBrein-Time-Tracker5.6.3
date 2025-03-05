<?php

namespace App\Http\Requests\Admin;

use App\Enums\EventEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserTimeLogStoreRequest extends FormRequest
{
    /*public function authorize()
    {
        // Разрешаем только админам
        //return $this->user() && $this->user()->role_id === 1;
    }
*/
    public function rules(): array
    {
        return [
            'id' => 'max:255',
            'user_id' => 'max:255',
            'acted_at' => 'max:255',
            'event_id' => Rule::in(EventEnum::values()),
        ];
    }
}
