<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role_id' => RoleEnum::class,
        'password' => 'hashed',
    ];

    protected $fillable = [
        'name',
        'login',
        'password',
        'role_id',
        'group_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Связь с группой
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    // Связь с рабочими днями
    public function workDays()
    {
        return $this->hasMany(UserWorkDay::class);
    }

    // Связь с логами времени
    public function timeLogs()
    {
        return $this->hasMany(UserTimeLog::class);
    }
}

