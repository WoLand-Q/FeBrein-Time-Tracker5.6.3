<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWorkDay extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'work_minutes',
        'break_minutes',
        'work_started_at',
        'work_ended_at',
        'break_started_at',
        'break_ended_at',
    ];
    protected $casts = [
        'work_started_at' => 'datetime',
        'break_started_at' => 'datetime',
        'break_ended_at' => 'datetime',
        'work_ended_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
