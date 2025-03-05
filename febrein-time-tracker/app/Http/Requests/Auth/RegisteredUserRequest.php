<?php

namespace App\Http\Requests\Auth;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
class RegisteredUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id' => ['required', 'integer'],
            'group_id' => ['required', 'integer'],
        ];
    }
}
