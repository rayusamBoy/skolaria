<?php

namespace App\Http\Requests;

use App\Helpers\Usr;
use App\Rules\StartsWithProperPhoneCode;
use Illuminate\Foundation\Http\FormRequest;

class UserInviteRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize()
    {
        return true;
    }

    /**
     * Get the user ID being acted upon.
     * This is used for unique validation rules to ignore the current user's record.
     *
     * @return int|string|null
     */
    public function userId()
    {
        return $this->user ?? $this->central_user ?? null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|string|unique:users|min:6|max:150',
            'password' => 'prohibited',
            'roles' => 'required',
            'gender' => 'nullable|string',
            'primary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', 'unique:users', new StartsWithProperPhoneCode],
            'secondary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', 'unique:users', new StartsWithProperPhoneCode],
            'email' => 'sometimes|nullable|email|max:100|unique:users',
            'username' => 'sometimes|nullable|alpha_dash|min:8|max:100|unique:users',
            'photo' => 'prohibited',
            'address' => 'nullable|string|min:3|max:120',
            'state_id' => 'nullable',
            'lga_id' => 'nullable',
            'nationality_id' => 'nullable',
            'occupation' => 'sometimes|nullable|string|max:150',
            'relative_name' => 'sometimes|nullable|string|min:6|max:150',
            'relation' => 'sometimes|nullable|string|min:4|max:150',
            'relative_primary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', 'unique:users', new StartsWithProperPhoneCode],
            'relative_secondary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', 'unique:users', new StartsWithProperPhoneCode],
            'primary_identification_number' => 'sometimes|nullable|string|unique:users|min:9|max:9',
            'secondary_identification_number' => 'sometimes|nullable|string|unique:users|min:23|max:23',
            'file_number' => 'sometimes|nullable|string|max:15',
            'bank_account_number' => 'sometimes|nullable|string|required_unless:bank_name,null|unique:staff_records|min:5|max:11',
            'bank_name' => 'sometimes|nullable|required_with:bank_account_number',
            'tin_number' => 'sometimes|nullable|alpha_num|unique:staff_records|min:3|max:9',
            'social_security_number' => 'sometimes|nullable|string|unique:staff_records|min:6|max:9',
            'licence_number' => 'sometimes|nullable|string|unique:staff_records|max:10',
            'place_of_living' => 'sometimes|nullable|string|max:100|min:3',
            'date_of_birth' => 'sometimes|nullable|date_format:'.Usr::getDateFormat(),
            // The regex allows for alphabetic characters and spaces within each subject (for example, "Literature in English" is valid).
            // Each subject can be followed by a comma and optional spaces, before the next valid subject.
            'subjects_studied' => 'sometimes|nullable|max:250|regex:/^([a-zA-Z\s]+)(,\s*[a-zA-Z\s]+)*$/',
        ];
    }

    public function attributes()
    {
        return [
            'nationality_id' => 'nationality',
            'state_id' => 'state',
            'lga_id' => 'LGA',
        ];
    }
}
