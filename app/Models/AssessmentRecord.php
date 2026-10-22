<?php

namespace App\Models;

use App\User;
use Iksaku\Laravel\MassUpdate\MassUpdatable;
use Illuminate\Database\Eloquent\Model as Eloquent;

class AssessmentRecord extends Eloquent
{
    use MassUpdatable;

    protected $fillable = ['cw1', 'cw2', 'cw3', 'cw4', 'cw5', 'cw6', 'cw7', 'cw8', 'cw9', 'cw10', 'hw1', 'hw2', 'hw3', 'hw4', 'hw5', 'tt1', 'tt2', 'tt3', 'tca', 'evaluated_exam_mark', 'tex1', 'tex2', 'tex3', 'total', 'class_position', 'section_position', 'subject_position', 'average', 'class_average', 'grade_id', 'year', 'subject_id', 'my_class_id', 'student_id', 'section_id', 'assessment_id', 'mark_id', 'af', 'ps', 't_comment', 'p_comment'];

    public function exam()
    {
        return $this->hasOneThrough(Exam::class, Assessment::class, 'id', 'id', 'assessment_id', 'exam_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function my_class()
    {
        return $this->belongsTo(MyClass::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }
}
