<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Division extends Eloquent
{
    protected $fillable = ['name', 'points_from', 'points_to', 'remarks', 'class_type_id'];

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function exam_records()
    {
        return $this->hasMany(ExamRecord::class);
    }

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLocked(Builder $query)
    {
        return $query->where('is_locked', true);
    }
}
