<?php

namespace App\Models;

use App\Traits\CentralDatabase;
use Nnjeim\World\Models\Country as CountryModel;

class Nationality extends CountryModel
{
    use CentralDatabase;
}
