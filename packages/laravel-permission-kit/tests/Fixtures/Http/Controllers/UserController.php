<?php

declare(strict_types=1);

namespace PermissionKit\Tests\Fixtures\Http\Controllers;

use Illuminate\Routing\Controller;
use PermissionKit\Concerns\AuthorizesPermissions;

class UserController extends Controller
{
    use AuthorizesPermissions;

    public function index()
    {
        return 'ok';
    }

    public function show($id)
    {
        return 'ok';
    }

    public function store()
    {
        return 'ok';
    }

    public function update($id)
    {
        return 'ok';
    }

    public function destroy($id)
    {
        return 'ok';
    }
}
