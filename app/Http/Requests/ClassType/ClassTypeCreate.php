<?php

namespace App\Http\Requests\ClassType;

use Illuminate\Foundation\Http\FormRequest;

class ClassTypeCreate extends FormRequest
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
            'name' => 'required|string|unique:class_types',
            'code' => 'required|unique:class_types',
            'minimum_subjects_required' => 'required|integer|min:0',
        ];
    }
}
