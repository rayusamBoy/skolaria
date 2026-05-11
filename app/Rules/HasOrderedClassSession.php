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
     * Expected file‑name structure:
     *   {Class_Name}{delimiter}{Second_Segment}{delimiter}{Year}
     * where Year is always "YYYY-YYYY" (the hyphen inside the year is constant).
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  Closure $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $delimiter = Mk::getDelimiter();
        $parts = explode($delimiter, $value);

        // Determine year and second segment
        if (\count($parts) >= 4 && is_numeric($parts[\count($parts) - 2]) && is_numeric($parts[\count($parts) - 1])) {
            $class_raw = $parts[0];
            $second_raw = $parts[1];
            $year_start = $parts[\count($parts) - 2];
            $year_end = $parts[\count($parts) - 1];
        } elseif (\count($parts) >= 3 && str_contains(end($parts), '-')) {
            $class_raw = $parts[0];
            $second_raw = $parts[1];
            $year_part = array_pop($parts);
            $year_segments = explode('-', $year_part);
            if (\count($year_segments) !== 2) {
                $fail('The :attribute contains an invalid year format.');
                return;
            }
            [$year_start, $year_end] = $year_segments;
        } else {
            $fail('The :attribute is invalid.');
            return;
        }

        $class_in_file = strtolower(str_replace('_', ' ', $class_raw));
        $year_in_file = "{$year_start}-{$year_end}";

        // Validate class
        $valid_class_names = MyClass::pluck('name')
            ->map(strtolower(...))
            ->toArray();

        if (!\in_array($class_in_file, $valid_class_names)) {
            $fail('The class specified in :attribute does not exist.');
            return;
        }

        // Validate year
        if ($year_in_file !== Mk::getCurrentSession()) {
            $fail('The session year in the :attribute does not match the current session.');
            return;
        }

        // Delegate second‑segment validation to the concrete rule
        $this->validateSecondSegment($second_raw, $fail);
    }

    /**
     * Validate the second segment of the file name (after the class name).
     *
     * @param  string  $second_raw  The raw second segment (may contain underscores)
     * @param  Closure $fail
     * @return void
     */
    abstract protected function validateSecondSegment(string $second_raw, Closure $fail): void;
}
