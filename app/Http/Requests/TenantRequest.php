<?php

namespace App\Http\Requests;

use App\Rules\Domain;
use Illuminate\Foundation\Http\FormRequest;

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
        ];

        $update = [
            'domain' => ['bail', 'unique:domains', 'required_if:identification_mode,domain', 'nullable', new Domain],
            'name' => 'required|string|max:255',
            'account_status' => 'required|string',
            'payment_status' => 'required|string',
            'remarks' => 'nullable|string|max:300',
            'tenant_slug' => 'required_if:identification_mode,request_data|nullable|string|alpha_dash',
        ];

        return $this->method() === 'POST' ? $store : $update;
    }
}
