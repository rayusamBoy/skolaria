<?php

namespace App\Http\Requests;

use App\Rules\UniqueDivisionRange;
use Illuminate\Foundation\Http\FormRequest;

class DivisionRequest extends FormRequest
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
            'points_from' => 'required|numeric|min:0|max:100',
            'points_to' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
                'gt:points_from',
                new UniqueDivisionRange(
                    ignoreId: $this->route('division') ?? $this->id,
                    classTypeId: $this->integer('class_type_id'),
                    name: $this->string('name')->toString(),
                    pointsFrom: $this->integer('points_from'),
                    pointsTo: $this->integer('points_to'),
                ),
            ],
        ];
    }
}
