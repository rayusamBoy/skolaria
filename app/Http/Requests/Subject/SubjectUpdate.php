<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class SubjectUpdate extends FormRequest
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
            'name' => 'required|string|min:3',
            'slug' => 'nullable|string|min:2|max:7',
            'core' => 'required|in:0,1'
        ];
    }

    public function attributes()
    {
        return [
            'slug' => 'Short Name',
            'core' => 'Core Subject'
        ];
    }
}
