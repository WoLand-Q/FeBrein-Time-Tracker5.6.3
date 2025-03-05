<?php
namespace App\Models;

use App\Enums\EventEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/** @property Carbon acted_at */
class UserTimeLog extends Model
{



    protected $fillable = [
        'user_id',
        'acted_at',
        'event_id',
    ];

    protected $casts = [
        'acted_at' => 'datetime', // Перетворення на Carbon при отриманні
        'event_id' => EventEnum::class,
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
