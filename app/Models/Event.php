<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Event extends Eloquent
{
    protected $fillable = ['name', 'description', 'year', 'month', 'day', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
