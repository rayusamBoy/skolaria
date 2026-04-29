<?php

namespace App\Models;

use Eloquent;

class Subject extends Eloquent
{
    protected $fillable = ['name', 'my_class_id', 'slug', 'is_core', 'credits'];

    protected $casts = [
        'is_core' => 'boolean'
    ];

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function record()
    {
        return $this->hasMany(SubjectRecord::class);
    }
}
