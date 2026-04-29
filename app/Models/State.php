<?php

namespace App\Models;

use App\Traits\CentralDatabase;
use Nnjeim\World\Models\State as StateModel;

class State extends StateModel
{
    use CentralDatabase;
}
