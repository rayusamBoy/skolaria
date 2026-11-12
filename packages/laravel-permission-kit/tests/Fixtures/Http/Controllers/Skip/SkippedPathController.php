<?php

declare(strict_types=1);

namespace PermissionKit\Tests\Fixtures\Http\Controllers\Skip;

use Illuminate\Routing\Controller;

class SkippedPathController extends Controller
{
    public function index() {}

    public function destroy($id) {}
}
