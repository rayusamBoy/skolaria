<?php

declare(strict_types=1);

namespace PermissionKit\Tests\Fixtures\Http\Controllers;

use Illuminate\Routing\Controller;

class SkipMeController extends Controller
{
    public function index() {}

    public function destroy($id) {}
}
