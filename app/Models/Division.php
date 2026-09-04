<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Division extends Eloquent
{
    protected $fillable = ['name', 'points_from', 'points_to', 'remarks', 'class_type_id'];

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }
}
