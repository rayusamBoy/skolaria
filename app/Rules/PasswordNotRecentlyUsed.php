<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class PasswordNotRecentlyUsed implements ValidationRule
{
    public function __construct(protected int $count = 3) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        if ($user->passwordUsedBefore((string) $value, $this->count)) {
            $fail('You have used this password recently. Please choose a different one.');
        }
    }
}
