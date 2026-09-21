<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Grade extends Eloquent
{
    use LogsActivity;

    protected $fillable = ['name', 'class_type_id', 'mark_from', 'mark_to', 'remarks', 'point'];

    protected $casts = [
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function exam_records()
    {
        return $this->hasMany(ExamRecord::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLocked(Builder $query)
    {
        return $query->where('is_locked', true);
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
