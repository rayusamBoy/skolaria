<?php

namespace App\Http\Requests;

use App\Helpers\Usr;
use App\Models\ParentRelative;
use App\Rules\StartsWithProperPhoneCode;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $store = [
            'name' => 'required|string|unique:users|min:6|max:150',
            'password' => 'nullable|string|min:3|max:50',
            'roles' => 'required',
            'gender' => 'required|string',
            'primary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', 'unique:users', new StartsWithProperPhoneCode],
            'secondary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', 'unique:users', new StartsWithProperPhoneCode],
            'email' => 'sometimes|nullable|email|max:100|unique:users',
            'username' => 'sometimes|nullable|alpha_dash|min:8|max:100|unique:users',
            'photo' => 'sometimes|nullable|image|mimes:jpeg,gif,png,jpg|max:2048',
            'address' => 'required|string|min:3|max:120',
            'state_id' => 'required',
            'lga_id' => 'required',
            'nationality_id' => 'required',
            'occupation' => 'sometimes|required|string|max:150',
            'relative_name' => 'sometimes|required|string|min:6|max:150',
            'relation' => 'sometimes|required|string|min:4|max:150',
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
            'date_of_birth' => 'sometimes|nullable|date_format:' . Usr::getDateFormat(),
            // The regex allows for alphabetic characters and spaces within each subject (for example, "Literature in English" is valid). 
            // Each subject can be followed by a comma and optional spaces, before the next valid subject.
            'subjects_studied' => 'sometimes|nullable|max:250|regex:/^([a-zA-Z\s]+)(,\s*[a-zA-Z\s]+)*$/',
        ];

        // Rules that apply to the user making changes to other user's records
        $update = [
            'name' => 'required|string|min:6|max:150',
            'gender' => 'required|string',
            'primary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', Rule::unique(User::class)->ignore($this->userId()), new StartsWithProperPhoneCode],
            'secondary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', Rule::unique(User::class)->ignore($this->userId()), new StartsWithProperPhoneCode],
            'relative_primary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', Rule::unique(ParentRelative::class)->ignore($this->userId()), new StartsWithProperPhoneCode],
            'relative_secondary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', Rule::unique(ParentRelative::class)->ignore($this->userId()), new StartsWithProperPhoneCode],
            'email' => ["sometimes", "nullable", "email", "max:100", Rule::unique(User::class)->ignore($this->userId())],
            'photo' => 'sometimes|nullable|image|mimes:jpeg,gif,png,jpg|max:2048',
            'address' => 'required|string|min:3|max:120',
            'state_id' => 'required',
            'lga_id' => 'required',
            'nationality_id' => 'required',
            'occupation' => 'sometimes|required|string|max:150',
            'relative_name' => 'sometimes|required|string|min:6|max:150',
            'relation' => 'sometimes|required|string|min:4|max:150',
            'bank_name' => 'sometimes|nullable|required_with:bank_account_number',
            'file_number' => 'sometimes|nullable|string|max:15',
            'primary_identification_number' => ['sometimes', 'nullable', 'alpha_num', 'min:3', 'max:20', Rule::unique(User::class)->ignore($this->userId())],
            'secondary_identification_number' => ['sometimes', 'nullable', 'alpha_num', 'min:3', 'max:20', Rule::unique(User::class)->ignore($this->userId())],
            'bank_account_number' => ['sometimes', 'nullable', 'string', 'min:5', 'max:11', 'required_unless:bank_name,null', Rule::unique('staff_records', 'bank_account_number')->ignore($this->userId(), 'user_id')],
            'tin_number' => ['sometimes', 'nullable', 'alpha_num', 'min:3', 'max:15', Rule::unique('staff_records')->ignore($this->userId(), 'user_id')],
            'social_security_number' => ['sometimes', 'nullable', 'string', 'min:3', 'max:15', Rule::unique('staff_records')->ignore($this->userId(), 'user_id')],
            'licence_number' => ['sometimes', 'nullable', 'string', 'max:10', Rule::unique('staff_records')->ignore($this->userId(), 'user_id')],
            'place_of_living' => 'sometimes|nullable|string|max:100|min:3',
            'date_of_birth' => 'sometimes|nullable|date_format:' . Usr::getDateFormat(),
            'subjects_studied' => 'sometimes|nullable|max:250|regex:/^([a-zA-Z\s]+)(,\s*[a-zA-Z\s]+)*$/',
        ];

        return ($this->method() === 'POST') ? $store : $update;
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
