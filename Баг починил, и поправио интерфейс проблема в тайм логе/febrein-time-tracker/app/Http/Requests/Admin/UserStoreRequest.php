<?php

namespace App\Http\Requests\Admin;

use App\Enums\RoleEnum;
use App\Http\Resources\Admin\GroupResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'max:255',
            'name' =>'max:255',
            'login' =>'max:255',
            'password' => 'max:255',
            'role_id' => Rule::in(RoleEnum::values()),
            'group_id' => 'required|exists:groups,id',
        ];
    }
}
