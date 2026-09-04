<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class ClassType extends Eloquent
{
    protected $fillable = ['name', 'code', 'minimum_subjects_required'];

    public function classes()
    {
        return $this->hasMany(MyClass::class, 'class_type_id');
    }

    public function scopeHasClasses()
    {
        return $this->classes()->exists();
    }
}
