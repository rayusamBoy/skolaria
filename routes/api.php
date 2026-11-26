<?php

use App\Http\Controllers\Api\V1\ReferenceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/reference')
  //  ->middleware(['auth:sanctum', 'abilities:reference-data:read'])
    ->controller(ReferenceController::class)
    ->group(function () {
        Route::get('/countries', 'countries');

        Route::get('/countries/{country}/states', 'states');

        Route::get('/countries/{country}/lgas', 'countryLgas');

        Route::get('/states/{state}/lgas', 'lgas');
    });
