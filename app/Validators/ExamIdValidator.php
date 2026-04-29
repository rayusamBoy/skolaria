<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ExamIdValidator
{
    /**
     * Validate the given exam ID.
     *
     * @param mixed $exam_id
     * @return void
     * @throws ValidationException
     */
    public static function validate($exam_id): void
    {
        if (empty($exam_id)) {
            return;
        }

        $validator = Validator::make(
            ['exam_id' => $exam_id],
            ['exam_id' => ['sometimes', 'nullable', 'exists:exams,id']],
            [],
            ['exam_id' => 'exam']
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
