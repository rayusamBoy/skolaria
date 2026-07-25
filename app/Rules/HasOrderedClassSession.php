<?php

namespace App\Rules;

use App\Helpers\Mk;
use App\Models\MyClass;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

abstract class HasOrderedClassSession implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Required file‑name structure:
     *   {Class}{delimiter}{SecondSegment}{delimiter}{TenantTitle…}{delimiter}{Year}{delimiter}{Timestamp}
     *
     * Year is always "YYYY-YYYY" (the hyphen inside the year is constant).
     * Timestamp is a numeric Unix timestamp at the very end.
     * Tenant title may contain the delimiter and is ignored.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $delimiter = Mk::getDelimiter();
        $parts = explode($delimiter, $value);

        if (\count($parts) < 5) {
            $fail('The :attribute does not match the required format.');
            return;
        }

        // Extract year (now the last part, must contain a hyphen)
        $yearPart = array_pop($parts);
        if (!str_contains($yearPart, '-')) {
            $fail('The :attribute is missing a valid session year segment.');
            return;
        }
        $yearSegments = explode('-', $yearPart);
        if (\count($yearSegments) !== 2) {
            $fail('The session year in :attribute is invalid.');
            return;
        }
        [$yearStart, $yearEnd] = $yearSegments;

        // After removing timestamp and year, we have at least [class, secondSegment, …tenantTitle…]
        if (\count($parts) < 2) {
            $fail('The :attribute is missing required segments.');
            return;
        }

        $classRaw = array_shift($parts);      // first part
        $secondRaw = array_shift($parts);      // second part
        // Remaining $parts = tenant title (ignored)

        // Validate class
        $classInFile = strtolower(str_replace('_', ' ', $classRaw));
        $validClassNames = MyClass::pluck('name')
            ->map(fn($name) => strtolower($name))
            ->toArray();

        if (!\in_array($classInFile, $validClassNames)) {
            $fail('The class specified in :attribute does not exist.');
            return;
        }

        // Validate session year
        $yearInFile = "{$yearStart}-{$yearEnd}";
        if ($yearInFile !== Mk::getCurrentSession()) {
            $fail('The session year in the :attribute does not match the current session.');
            return;
        }

        // Delegate second‑segment validation
        $this->validateSecondSegment($secondRaw, $fail);
    }

    /**
     * Validate the second segment of the file name (after the class name).
     */
    abstract protected function validateSecondSegment(string $secondRaw, Closure $fail): void;
}
