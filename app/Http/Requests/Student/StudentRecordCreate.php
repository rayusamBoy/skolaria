<?php

namespace App\Http\Requests\Student;

use App\Helpers\Usr;
use App\Rules\StartsWithProperPhoneCode;
use Illuminate\Foundation\Http\FormRequest;

class StudentRecordCreate extends FormRequest
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
            'name' => 'required|string|min:6|max:150',
            'admission_number' => 'sometimes|nullable|regex:/^[a-zA-Z0-9\s\-\/]+$/|min:4|max:20|unique:student_records',
            'gender' => 'required|string',
            'date_admitted' => 'required|date_format:' . Usr::getDateFormat(),
            'primary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', new StartsWithProperPhoneCode],
            'secondary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', new StartsWithProperPhoneCode],
            'email' => 'sometimes|nullable|email|max:100|unique:users',
            'photo' => 'sometimes|nullable|image|mimes:jpeg,gif,png,jpg|max:2048',
            'address' => 'required|string|min:4|max:120',
            'blood_group_id' => 'sometimes|nullable',
            'state_id' => 'required',
            'lga_id' => 'required',
            'nationality_id' => 'required',
            'my_class_id' => 'required',
            'section_id' => 'required',
            'my_parent_id' => 'sometimes|nullable',
            'dorm_id' => 'sometimes|nullable',
            'primary_school_name' => 'required|string|max:40',
            'secondary_school_name' => 'sometimes|nullable|string|max:40',
            'birth_certificate' => 'sometimes|nullable|file|mimes:pdf,png,jpg,jpeg|max:2048',
            'disability' => 'sometimes|nullable',
            'chronic_health_problems' => 'sometimes|nullable|string',
            'food_taboos' => 'sometimes|nullable|string',
            'parent_status' => 'required|string',
            'date_of_birth' => 'required|date_format:' . Usr::getDateFormat(),
            'religion' => 'required'
        ];
    }

    public function attributes()
    {
        return [
            'section_id' => 'section',
            'nationality_id' => 'nationality',
            'my_class_id' => 'class',
            'dorm_id' => 'dormitory',
            'state_id' => 'state',
            'lga_id' => 'LGA',
            'blood_group_id' => 'blood group',
            'my_parent_id' => 'parent',
        ];
    }

    protected function getValidatorInstance()
    {
        $input = $this->all();

        $input['my_parent_id'] = $input['my_parent_id'] ? Usr::decodeHash($input['my_parent_id']) : null;

        $this->getInputSource()->replace($input);

        return parent::getValidatorInstance();
    }
}
