<?php

namespace App\Rules;

use App\Models\Nationality;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class StartsWithProperPhoneCode implements ValidationRule, DataAwareRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!isset($this->data['nationality_id']) && !isset($this->data[0]['nationality_id'])) {
            $fail("The :attribute cannot be validated because Nationality is not set.");
            return;
        }

        // Attempt to get the ID from the data (as usual for form request validation), 
        // or take one value for Excel validation, since the Excel package validates in batches 
        // and the value is the same for all rows.
        $nationality_id = $this->data['nationality_id'] ?? array_column($this->data, 'nationality_id')[0];
        $phone_code = Nationality::where('id', $nationality_id)->value('phone_code');
        $phone_code = "+$phone_code";

        if (!Str::startsWith($value, $phone_code))
            $fail("The :attribute must start with proper phone code: $phone_code.");
    }

    /**
     * All of the data under validation.
     * 
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}
