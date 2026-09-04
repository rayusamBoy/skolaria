<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StudentRecord extends Eloquent
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'session',
        'user_id',
        'my_class_id',
        'section_id',
        'my_parent_id',
        'dorm_id',
        'dorm_room_no',
        'admission_number',
        'date_admitted',
        'is_graduated',
        'graduated_session',
        'house_number',
        'age',
        'primary_school_name',
        'secondary_school_name',
        'birth_certificate',
        'disability',
        'chronic_health_problems',
        'parent_status',
        'food_taboos'
    ];

    protected $casts = [
        'is_graduated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function my_parent()
    {
        return $this->belongsTo(User::class);
    }

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function dorm()
    {
        return $this->belongsTo(Dorm::class);
    }

    public function promotion()
    {
        return $this->hasMany(Promotion::class, 'student_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->dontSubmitEmptyLogs()
            ->logOnlyDirty();
    }
}
