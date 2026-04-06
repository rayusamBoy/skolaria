<?php

namespace App\Http\Requests\Subject;

use Illuminate\Foundation\Http\FormRequest;

class SubjectCreate extends FormRequest
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
            'my_class_id' => 'required',
            'slug' => 'nullable|string|min:2|max:7',
        ];
    }

    public function attributes()
    {
        return  [
            'my_class_id' => 'class',
            'slug' => 'short name',
        ];
    }
}
