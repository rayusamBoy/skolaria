<?php

namespace App\Http\Requests\MyClass;

use App\Models\MyClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassCreate extends FormRequest
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
                Rule::unique(MyClass::class)->where(fn($query) => $query->where('name', $this->name)->where('class_type_id', $this->class_type_id))
            ],
            'class_type_id' => 'required|exists:class_types,id',
        ];
    }

    public function attributes()
    {
        return [
            'class_type_id' => 'Class Type',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'The name has already been taken for the selected class type.',
        ];
    }
}
