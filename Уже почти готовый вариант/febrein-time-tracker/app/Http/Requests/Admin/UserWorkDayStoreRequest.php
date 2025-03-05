<?php

namespace App\Http\Requests\Admin;

use App\Enums\EventEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserWorkDayStoreRequest extends FormRequest
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
            'work_started_at' => 'max:255',
            'break_started_at' => 'max:255',
            'break_ended_at' => 'max:255',
            'work_ended_at' => 'max:255',
        ];
    }
}
