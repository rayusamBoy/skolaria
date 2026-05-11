<?php

namespace App\Http\Requests;

use App\Helpers\Usr;
use App\Models\StaffRecord;
use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserProfileUpdate extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    protected function getAuthUserId()
    {
        return auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'photo' => 'sometimes|nullable|image|mimes:jpeg,gif,png,jpg|max:2048',
            'address' => 'required|string|min:3|max:120',
            'bank_name' => 'sometimes|nullable|required_with:bank_account_number',
            'file_number' => 'sometimes|nullable|string|max:15',
            'primary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', Rule::unique(User::class)->ignore($this->getAuthUserId(), 'id')],
            'secondary_mobile' => ['sometimes', 'nullable', 'string', 'max:16', Rule::unique(User::class)->ignore($this->getAuthUserId(), 'id')],
            'email' => ['sometimes', 'nullable', 'email', 'max:100', Rule::unique(User::class, 'email')->ignore($this->getAuthUserId(), 'id')],
            'username' => ['sometimes', 'nullable', 'alpha_dash', 'min:8', 'max:100', Rule::unique(User::class, 'username')->ignore($this->getAuthUserId(), 'id')],
            'primary_identification_number' => ['sometimes', 'nullable', 'alpha_num', 'min:3', 'max:20', Rule::unique(User::class, 'primary_identification_number')->ignore($this->getAuthUserId(), 'id')],
            'secondary_identification_number' => ['sometimes', 'nullable', 'alpha_num', 'min:3', 'max:20', Rule::unique(User::class, 'secondary_identification_number')->ignore($this->getAuthUserId(), 'id')],
            'bank_account_number' => ['sometimes', 'nullable', 'string', 'min:5', 'max:11', Rule::unique(StaffRecord::class, 'bank_account_number')->ignore($this->getAuthUserId(), 'user_id')],
            'tin_number' => ['sometimes', 'nullable', 'alpha_num', 'min:3', 'max:15', Rule::unique(StaffRecord::class, 'tin_number')->ignore($this->getAuthUserId(), 'user_id')],
            'social_security_number' => ['sometimes', 'nullable', 'string', 'min:3', 'max:15', Rule::unique(StaffRecord::class, 'social_security_number')->ignore($this->getAuthUserId(), 'user_id')],
            'licence_number' => ['sometimes', 'nullable', 'string', 'max:10', Rule::unique(StaffRecord::class, 'licence_number')->ignore($this->getAuthUserId(), 'user_id')],
            'date_of_birth' => 'sometimes|nullable|date_format:' . Usr::getDateFormat(),
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
