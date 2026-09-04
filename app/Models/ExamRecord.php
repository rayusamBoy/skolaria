<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Iksaku\Laravel\MassUpdate\MassUpdatable;

class ExamRecord extends Eloquent
{
    use MassUpdatable;

    protected $fillable = ['exam_id', 'my_class_id', 'student_id', 'section_id', 'af', 'ps', 't_comment', 'p_comment', 'year', 'total', 'average', 'class_average', 'section_position', 'class_position', 'grade_id', 'points', 'gpa', 'division_id', 'exam_number'];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }
}
