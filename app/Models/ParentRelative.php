<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ParentRelative extends Eloquent
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'primary_mobile', 'secondary_mobile', 'relation'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
