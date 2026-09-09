<?php

namespace App\Http\Requests\Grade;

use App\Models\Grade as GradeModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeUpdate extends FormRequest
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
            'name' => ['required', 'string', Rule::unique(GradeModel::class)->where(fn ($query) => $query->where('name', $this->name)->where('class_type_id', $this->class_type_id))->ignore($this->grade)],
            'mark_from' => 'required|numeric|max:100|min:0',
            'mark_to' => 'required|numeric|max:100|min:0|gt:mark_from',
            'point' => 'required|numeric|max:50|min:0',
        ];
    }

    public function messages()
    {
        return [
            'name.unique' => 'The name has already been taken for the selected class type.',
        ];
    }
}
