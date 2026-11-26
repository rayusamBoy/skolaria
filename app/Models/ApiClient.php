<?php

namespace App\Models;

use App\Traits\CentralDatabase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Sanctum\HasApiTokens;

class ApiClient extends Model
{
    use CentralDatabase;
    use HasApiTokens;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'tenant_id',
        'is_active',
        'last_used_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Determine whether this API client is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Determine whether this client is associated with a tenant.
     */
    public function isTenantScoped(): bool
    {
        return $this->tenant_id !== null;
    }

    /**
     * Get the API client's personal access tokens.
     *
     * API client tokens are always stored centrally,
     * independently of tenant database connections.
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(
            CentralPersonalAccessToken::class,
            'tokenable'
        );
    }
}
