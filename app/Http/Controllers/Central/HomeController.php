<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\Request as HttpReq;

class HomeController extends Controller
{
    protected $user, $tenant;
    public function __construct(UserRepo $user, TenantRepo $tenant)
    {
        $this->user = $user;
        $this->tenant = $tenant;
    }

    public function index()
    {
        return redirect()->route('central_dashboard');
    }

    public function dashboard(HttpReq $request)
    {
        $d['tenants'] = $this->tenant->getAll();
        $d['users'] = $this->user->all();

        return view('pages.central.dashboard', $d);
    }
}
