<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected UserRepo $user;
    protected TenantRepo $tenant;

    public function __construct(UserRepo $user, TenantRepo $tenant)
    {
        $this->user = $user;
        $this->tenant = $tenant;
    }

    public function index()
    {
        return redirect()->route('central_dashboard');
    }

    public function dashboard(Request $request)
    {
        $data['tenants'] = $this->tenant->getAll();
        $data['users'] = $this->user->all();

        return view('pages.central.dashboard', $data);
    }
}
