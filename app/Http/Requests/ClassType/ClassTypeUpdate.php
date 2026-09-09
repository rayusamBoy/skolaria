<?php

namespace App\Http\Requests\ClassType;

use Illuminate\Foundation\Http\FormRequest;

class ClassTypeUpdate extends FormRequest
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
            'name' => "required|string|unique:class_types,name,{$this->class_type->id}",
            'code' => "required|unique:class_types,code,{$this->class_type->id}",
            'minimum_subjects_required' => 'required|integer|min:0',
        ];
    }
}
