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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && now()->gt($this->expires_at);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }
}
