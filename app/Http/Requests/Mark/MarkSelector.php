<?php

namespace App\Http\Requests\Mark;

use Illuminate\Foundation\Http\FormRequest;

class MarkSelector extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'exam_id' => 'required|exists:exams,id',
            'my_class_id' => 'required|exists:my_classes,id',
            'section_id' => 'nullable|sometimes|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'selected' => 'sometimes|nullable|boolean',
        ];
    }

    public function attributes()
    {
        return [
            'exam_id' => 'exam',
            'my_class_id' => 'class',
            'section_id' => 'section',
            'subject_id' => 'subject',
        ];
    }
}
