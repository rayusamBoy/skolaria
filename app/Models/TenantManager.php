<?php

namespace App\Models;

use App\Traits\CentralDatabase;
use Illuminate\Database\Eloquent\Model;

class TenantManager extends Model
{
    use CentralDatabase;

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
