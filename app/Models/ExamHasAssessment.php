<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamHasAssessment extends Model
{
    protected $fillable = ['exam_id', 'assessment_id'];

    protected $table = 'exam_has_assessment';

    public $timestamps = false;

    public function exam()
    {
        return $this->hasOne(Exam::class);
    }

    public function assessment()
    {
        return $this->hasOne(Assessment::class);
    }
}
