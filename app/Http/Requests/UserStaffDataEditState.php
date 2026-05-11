<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStaffDataEditState extends FormRequest
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
            'id' => 'required|integer|bail',
            'allow_staff_data_edit' => 'required|boolean',
        ];
    }
}
