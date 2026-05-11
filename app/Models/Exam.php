<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Exam extends Eloquent
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'term',
        'year',
        'category_id',
        'is_published',
        'is_locked',
        'class_type_id',
        'exam_denominator',
        'exam_student_position_by_value',
        'ca_student_position_by_value',
        'cw_denominator',
        'hw_denominator',
        'tt_denominator',
        'tdt_denominator',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_locked' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ExamCategory::class, 'category_id');
    }

    public function number_format()
    {
        return $this->hasMany(ExamNumberFormat::class);
    }

    public function class_type()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function assessment()
    {
        return $this->hasOneThrough(Assessment::class, ExamHasAssessment::class, 'exam_id', 'id', 'id', 'assessment_id');
    }

    public function scopeHasAssessment()
    {
        return $this->assessment()->exists();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
