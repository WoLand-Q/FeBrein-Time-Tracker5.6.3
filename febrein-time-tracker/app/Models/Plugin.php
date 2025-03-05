<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    protected $fillable = [
        'slug',         // Уникальный идентификатор, напр. "profile"
        'name',         // Отображаемое имя плагина
        'description',  // Описание плагина (необязательно)
        'version',      // Версия, напр. "1.0.0"
        'author',       // Автор плагина
        'type',         // Тип плагина: 'backend', 'frontend' или 'fullstack'
        'enabled',      // Флаг: включён ли плагин (bool)
    ];
}
