<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'group_name',
    ];
    public $timestamps = true;
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
