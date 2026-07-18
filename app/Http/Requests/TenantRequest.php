<?php

namespace App\Http\Requests;

use App\Rules\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    protected function getRouteTenantIdParameter()
    {
        // If not data store
        if ($this->method() !== "POST")
            return $this->route('tenant');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $store = [
            'domain' => ['bail', 'unique:domains', 'required_if:identification_mode,domain', 'nullable', new Domain],
            'name' => 'required|string|unique:tenants,data->name',
            'remarks' => 'sometimes|nullable|max:300',
            'account_status' => 'required|string',
            'payment_status' => 'required|string',
            'identification_mode' => 'required|in:domain,request_data',
            'tenant_slug' => 'required_if:identification_mode,request_data|nullable|string|alpha_dash',
            'lock_master_data' => [
                'nullable',
                'in:0,1',
                Rule::requiredIf(
                    fn() =>
                    $this->identification_mode === 'request_data'
                    && (int) $this->is_template === 0
                ),
                function ($attribute, $value, $fail) {
                    if ((int) $this->is_template === 1 && (int) $value !== 0) {
                        $fail('Lock Master Data must be set to No when the Tenant is a Template.');
                    }
                },
            ],

            'is_template' => [
                'required_if:identification_mode,request_data',
                'in:0,1',
            ],
        ];

        $update = [
            'domain' => ['bail', 'unique:domains', 'required_if:identification_mode,domain', 'nullable', new Domain],
            'name' => 'required|string|max:255',
            'account_status' => 'required|string',
            'payment_status' => 'required|string',
            'remarks' => 'nullable|string|max:300',
            'tenant_slug' => 'required_if:identification_mode,request_data|nullable|string|alpha_dash',
            'lock_master_data' => [
                'nullable',
                'in:0,1',
                Rule::requiredIf(
                    fn() =>
                    $this->identification_mode === 'request_data'
                    && (int) $this->is_template === 0
                ),
                function ($attribute, $value, $fail) {
                    if ((int) $this->is_template === 1 && (int) $value !== 0) {
                        $fail('Lock Master Data must be set to No when the Tenant is a Template.');
                    }
                },
            ],

            'is_template' => [
                'required_if:identification_mode,request_data',
                'in:0,1',
            ],
        ];

        return $this->method() === 'POST' ? $store : $update;
    }
}
