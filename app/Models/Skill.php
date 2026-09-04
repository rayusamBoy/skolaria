<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Skill extends Eloquent
{
    protected  $fillable = ['name', 'skill_type', 'class_type'];
}
