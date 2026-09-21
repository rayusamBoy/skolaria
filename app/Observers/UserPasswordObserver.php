<?php

namespace App\Observers;

use App\Helpers\Qs;
use App\Models\PasswordHistory;
use App\User;

class UserPasswordObserver
{
    /**
     * Record the *old* password before it is replaced.
     */
    public function updating(User $user): void
    {
        if (! $user->isDirty('password')) {
            return;
        }

        $original = $user->getOriginal('password');

        if ($original) {
            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $original,
            ]);
        }

        $this->pruneHistory($user);
    }

    /**
     * Record the initial password when a user is created.
     */
    public function created(User $user): void
    {
        if ($user->password) {
            PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $user->password,
            ]);
        }
    }

    /**
     * Keep only the required number of history rows (plus one for safety).
     */
    protected function pruneHistory(User $user): void
    {
        $keep = max((int) Qs::getSetting('password_history_count', true), 1) + 1;

        $ids = PasswordHistory::where('user_id', $user->id)
            ->latest('created_at')
            ->pluck('id');

        if ($ids->count() > $keep) {
            PasswordHistory::whereIn('id', $ids->slice($keep))->delete();
        }
    }
}
