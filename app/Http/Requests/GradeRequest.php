<?php

namespace App\Http\Requests;

use App\Rules\UniqueGradeRange;
use Illuminate\Foundation\Http\FormRequest;

class GradeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'class_type_id' => ['required', 'integer', 'exists:class_types,id'],
            'mark_from' => 'required|numeric|min:0|max:100',
            'mark_to' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'gt:mark_from',
                new UniqueGradeRange(
                    ignoreId: $this->route('grade') ?? $this->id,
                    classTypeId: $this->integer('class_type_id'),
                    name: $this->string('name')->toString(),
                    markFrom: $this->integer('mark_from'),
                    markTo: $this->integer('mark_to'),
                ),
            ],
            'point' => 'required|numeric|min:0|max:50',
        ];
    }
}
