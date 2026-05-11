<?php

namespace App\Models;

use App\User;
use Eloquent;

class Promotion extends Eloquent
{
    protected $fillable = ['previous_my_class_id', 'previous_section_id', 'new_my_class_id', 'new_section_id', 'is_graduated', 'student_id', 'user_id', 'from_session', 'to_session', 'status', 'remarks'];

    protected $casts = [
        'is_graduated' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(StudentRecord::class, 'student_id');
    }

    public function fc()
    {
        return $this->belongsTo(MyClass::class, 'previous_my_class_id');
    }

    public function fs()
    {
        return $this->belongsTo(Section::class, 'previous_section_id');
    }

    public function ts()
    {
        return $this->belongsTo(Section::class, 'new_section_id');
    }

    public function tc()
    {
        return $this->belongsTo(MyClass::class, 'new_my_class_id');
    }
}
