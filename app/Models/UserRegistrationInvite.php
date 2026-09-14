<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserRegistrationInvite extends Model
{
    protected $fillable = [
        'token',
        'roles',
        'prefilled_data',
        'submitted_data',
        'status',
        'expires_at',
        'submitted_at',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'roles' => 'array',
        'prefilled_data' => 'array',
        'submitted_data' => 'array',
        'expires_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public const string STATUS_PENDING = 'pending';

    public const string STATUS_SUBMITTED = 'submitted';

    public const string STATUS_APPROVED = 'approved';

    public const string STATUS_REJECTED = 'rejected';

    public const string STATUS_CANCELLED = 'cancelled';

    /**
     * Bootstrap badge color for each status.
     */
    public const array STATUS_COLORS = [
        self::STATUS_PENDING => 'warning',
        self::STATUS_SUBMITTED => 'info',
        self::STATUS_APPROVED => 'success',
        self::STATUS_REJECTED => 'danger',
        self::STATUS_CANCELLED => 'secondary',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the invitation has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }

    /**
     * Check if the invitation is still pending (not submitted, approved, rejected, or cancelled).
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && ! $this->isExpired();
    }

    /**
     * Check if the invitation has been approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the invitation has been rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if the invitation has been submitted.
     */
    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED && $this->submitted_at !== null;
    }

    /**
     * Check if the invitation has been cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Invitation is in a non-terminal state (can still be acted on).
     */
    public function isActionable(): bool
    {
        return \in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_SUBMITTED,
        ], true);
    }

    /**
     * Invitation has reached a terminal state.
     */
    public function isTerminal(): bool
    {
        return \in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ], true);
    }

    /**
     * Can the invite be approved? Only after the user has submitted the form.
     */
    public function canBeApproved(): bool
    {
        return $this->isSubmitted();
    }

    /**
     * Can the invite be rejected? Only after the user has submitted the form.
     */
    public function canBeRejected(): bool
    {
        return $this->isSubmitted();
    }

    /**
     * Can the invite be cancelled? While pending (even if expired) or submitted.
     */
    public function canBeCancelled(): bool
    {
        return \in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_SUBMITTED,
        ], true);
    }

    /**
     * Is the registration URL still usable (not expired, not submitted, still pending)?
     */
    public function hasUsableUrl(): bool
    {
        return $this->status === self::STATUS_PENDING
            && ! $this->isExpired()
            && ! $this->submitted_data;
    }

    /**
     * Human-readable status label. Pending + expired collapses to "Expired".
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->status === self::STATUS_PENDING && $this->isExpired()) {
            return 'Expired';
        }

        return ucfirst($this->status);
    }

    /**
     * Bootstrap color for the current status.
     * Pending + expired is shown as a muted "secondary".
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->status === self::STATUS_PENDING && $this->isExpired()) {
            return 'secondary';
        }

        return self::STATUS_COLORS[$this->status] ?? 'default';
    }
}
