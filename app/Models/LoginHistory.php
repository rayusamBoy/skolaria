<?php

namespace App\Models;

use App\User;
use Eloquent;

class LoginHistory extends Eloquent
{
    protected $fillable = ['user_id', 'ip_address', 'user_agent', 'logged_in_at'];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
