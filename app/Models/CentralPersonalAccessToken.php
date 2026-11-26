<?php

namespace App\Models;

use App\Traits\CentralDatabase;
use Laravel\Sanctum\PersonalAccessToken;

class CentralPersonalAccessToken extends PersonalAccessToken
{
    use CentralDatabase;

    protected $table = 'personal_access_tokens';
}
