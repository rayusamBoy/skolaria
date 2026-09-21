<?php

namespace App\Services;

use App\Helpers\Qs;
use App\Rules\PasswordNotRecentlyUsed;
use Illuminate\Validation\Rules\Password;

class PasswordPolicyService
{
    /**
     * Build the base password rule (without `confirmed`).
     */
    public function baseRule(): Password
    {
        $rule = Password::min($this->minLength());

        if ($this->requireLetters()) {
            $rule->letters();
        }
        if ($this->requireMixedCase()) {
            $rule->mixedCase();
        }
        if ($this->requireNumbers()) {
            $rule->numbers();
        }
        if ($this->requireSymbols()) {
            $rule->symbols();
        }
        if ($this->requireUncompromised()) {
            $rule->uncompromised();
        }

        return $rule;
    }

    /**
     * Full rule array for validation.
     *
     * @param  bool  $with_confirmation  Whether to require `password_confirmation`.
     * @return array<int, mixed>
     */
    public function rules(?bool $with_confirmation = null): array
    {
        $with_confirmation ??= $this->requireConfirmation();

        $rules = ['required', 'string'];

        if ($with_confirmation) {
            $rules[] = 'confirmed';
        }

        $rules[] = $this->baseRule();

        $history = $this->historyCount();
        if ($history > 0) {
            $rules[] = new PasswordNotRecentlyUsed($history);
        }

        return $rules;
    }

    // ------------------------------------------------------------------
    // Individual policy flags (all fall back to sane defaults)
    // ------------------------------------------------------------------

    public function minLength(): int
    {
        return max((int) Qs::getSetting('password_min_length'), 6) ?: 8;
    }

    public function requireConfirmation(): bool
    {
        return (bool) Qs::getSetting('password_require_confirmation');
    }

    public function requireLetters(): bool
    {
        return (bool) Qs::getSetting('password_require_letters');
    }

    public function requireMixedCase(): bool
    {
        return (bool) Qs::getSetting('password_require_mixed_case');
    }

    public function requireNumbers(): bool
    {
        return (bool) Qs::getSetting('password_require_numbers');
    }

    public function requireSymbols(): bool
    {
        return (bool) Qs::getSetting('password_require_symbols');
    }

    public function requireUncompromised(): bool
    {
        return (bool) Qs::getSetting('password_require_uncompromised');
    }

    public function historyCount(): int
    {
        return max((int) Qs::getSetting('password_history_count'), 0);
    }
}
