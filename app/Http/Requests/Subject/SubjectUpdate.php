<?php

namespace App\Http\Requests\Subject;

use App\Models\Subject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'name' => [
                'required',
                'string',
                'min:3',
                Rule::unique(Subject::class)->where(fn($query) => $query->where('my_class_id', $this->my_class_id)->where('name', $this->name))->ignore($this->subject)
            ],
            'slug' => 'nullable|string|min:2|max:7',
            'is_core' => 'required|in:0,1',
            'credits' => 'required|numeric|max:50|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'The name has already been taken for the selected class.',
        ];
    }
}
