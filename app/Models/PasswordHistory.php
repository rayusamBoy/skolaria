<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
