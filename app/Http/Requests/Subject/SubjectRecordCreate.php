<?php

namespace App\Http\Requests\Subject;

use App\Helpers\Qs;
use Illuminate\Foundation\Http\FormRequest;

class SubjectRecordCreate extends FormRequest
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
            'section_id' => 'nullable|prohibited_unless:students_ids,null',
            'students_ids' => 'nullable',
            'teacher_id' => 'required',
        ];
    }

    public function attributes()
    {
        return [
            'teacher_id' => 'teacher',
            'section_id' => 'section',
            'students_ids' => 'students',
        ];
    }

    protected function getValidatorInstance()
    {
        $input = $this->all();

        $input['teacher_id'] = $input['teacher_id'] ? Qs::decodeHash($input['teacher_id']) : null;

        $this->getInputSource()->replace($input);

        return parent::getValidatorInstance();
    }
}
