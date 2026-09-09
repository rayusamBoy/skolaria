<?php

namespace App\Http\Requests\MyClass;

use App\Models\MyClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassUpdate extends FormRequest
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
                'min:2',
                Rule::unique(MyClass::class)->where(fn ($query) => $query->where('name', $this->name)->where('class_type_id', $this->class_type_id))->ignore($this->class),
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'The name has already been taken for the class type.',
        ];
    }
}
