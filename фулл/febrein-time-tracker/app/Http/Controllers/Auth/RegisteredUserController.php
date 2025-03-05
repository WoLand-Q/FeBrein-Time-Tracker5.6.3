<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisteredUserRequest;
use App\Http\Resources\Auth\RegisteredUserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Psy\Util\Json;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisteredUserRequest $request)
    {
        $login = $request->input('login');
        $exists = User::where('login', $login)->exists();
        if ($exists) {
            return response()->json('Login already exists');
        }

        $resource = RegisteredUserResource::make($request->validated());
        $data = $resource->toArray($request);
        $user = User::create($data);
        event(new Registered($user));
        Auth::login($user);

        return response()->json('Done');
    }
}
