<?php

namespace App\Http\Requests\Division;

use Illuminate\Foundation\Http\FormRequest;

class DivisionCreate extends FormRequest
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
            'name' => 'required|string',
            'points_from' => 'required|numeric|min:0',
            'points_to' => 'required|numeric|gt:points_from|min:0',
        ];
    }
}
