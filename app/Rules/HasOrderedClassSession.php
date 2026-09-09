<?php

namespace App\Rules;

use App\Helpers\Mk;
use App\Models\MyClass;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

abstract class HasOrderedClassSession implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $delimiter = Mk::getDelimiter();

        // Find the session year (always YYYY-YYYY)
        if (! preg_match('/\b(\d{4})-(\d{4})\b/', $value, $matches, PREG_OFFSET_CAPTURE)) {
            $fail('The :attribute does not contain a valid session year.');

            return;
        }

        $yearStart = $matches[1][0];
        $yearEnd = $matches[2][0];
        $yearOffset = $matches[0][1];
        $yearLength = \strlen($matches[0][0]);

        // Split the string into two halves: before the year and after the year
        $left = trim(substr($value, 0, $yearOffset), $delimiter);
        $right = trim(substr($value, $yearOffset + $yearLength), $delimiter);

        // Left side must contain at least class + exam name
        if (empty($left)) {
            $fail('The :attribute is missing class and examination name.');

            return;
        }

        $leftParts = explode($delimiter, $left);
        $leftParts = array_values(array_filter($leftParts, fn ($p) => $p !== ''));

        if (\count($leftParts) < 2) {
            $fail('The :attribute must contain a class and an examination name before the year.');

            return;
        }

        $classRaw = array_shift($leftParts);
        $secondRaw = array_shift($leftParts);
        // Any remaining $leftParts were part of the exam name (if delimiter appeared inside it)
        if (! empty($leftParts)) {
            $secondRaw .= $delimiter.implode($delimiter, $leftParts);
        }

        // Right side is the tenant title (may be empty)
        $tenantTitle = $right;

        // Validate class name
        $classInFile = strtolower(str_replace('_', ' ', $classRaw));
        $validClassNames = MyClass::pluck('name')
            ->map(fn ($name) => strtolower($name))
            ->toArray();

        if (! \in_array($classInFile, $validClassNames)) {
            $fail('The class specified in :attribute is invalid.');

            return;
        }

        // Validate session year
        $yearInFile = "{$yearStart}-{$yearEnd}";
        if ($yearInFile !== Mk::getCurrentSession()) {
            $fail('The session year in :attribute does not match the current session.');

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
