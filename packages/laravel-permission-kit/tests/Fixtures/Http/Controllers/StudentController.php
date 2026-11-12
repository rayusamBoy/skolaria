<?php

declare(strict_types=1);

namespace PermissionKit\Tests\Fixtures\Http\Controllers;

use Illuminate\Routing\Controller;
use PermissionKit\Attributes\Permission;
use PermissionKit\Attributes\SkipPermission;

class StudentController extends Controller
{
    public function index() {}

    public function show($id) {}

    #[SkipPermission]
    public function ping() {}

    #[Permission('students.export', 'Export Students', 'Download a CSV of students')]
    public function export() {}

    public function update_lock_state($id) {}

    public function updateLockState() {}

    public function getHTMLContent() {}
}
