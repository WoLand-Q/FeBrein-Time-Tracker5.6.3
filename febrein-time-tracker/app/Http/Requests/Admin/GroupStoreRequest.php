<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GroupStoreRequest extends FormRequest
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
            'group_name' => 'max:255',
        ];
    }
}
