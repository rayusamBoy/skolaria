<?php

namespace App\Rules;

use Closure;

class HasOrderedClassDescriptionSession extends HasOrderedClassSession
{
    protected string $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateSecondSegment(string $second_raw, Closure $fail): void
    {
        $description_in_file = strtolower(str_replace('_', ' ', $second_raw));

        if ($description_in_file !== strtolower($this->description)) {
            $fail('The description in the :attribute does not match the expected content.');
        }
    }
}
