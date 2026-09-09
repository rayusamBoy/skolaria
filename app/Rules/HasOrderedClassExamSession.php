<?php

namespace App\Rules;

use App\Models\Exam;
use Closure;

class HasOrderedClassExamSession extends HasOrderedClassSession
{
    /**
     * {@inheritdoc}
     */
    protected function validateSecondSegment(string $second_raw, Closure $fail): void
    {
        $exam_in_file = strtolower(str_replace('_', ' ', $second_raw));

        $valid_exam_names = Exam::pluck('name')
            ->map(strtolower(...))
            ->toArray();

        if (! \in_array($exam_in_file, $valid_exam_names)) {
            $fail('The exam specified in the :attribute does not exist.');
        }
    }
}
