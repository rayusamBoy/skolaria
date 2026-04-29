<?php

namespace App\Traits;

trait CentralDatabase
{
    /**
     * Override the default database connection for models using this trait due to a compatibility issue.
     *
     * "Declaration of Stancl\Tenancy\Database\Concerns\CentralConnection::getConnectionName() must be compatible with
     * Nnjeim\World\Models\Country::getConnectionName(): string"
     *
     * This issue occurs when using models that extend Nnjeim\World\Models\*, which define getConnectionName()
     * with a strict string return type.
     *
     * By defining getConnectionName() with a string return type in this trait, we ensure compatibility with
     * all Nnjeim\World\Models classes while avoiding method signature conflicts in inherited models.
     *
     * This workaround can be removed once the underlying issue is resolved in the stancl/tenancy package,
     * or if we migrate away from nnjeim/world package.
     */
    public function getConnectionName(): string
    {
        return config('tenancy.database.central_connection');
    }
}