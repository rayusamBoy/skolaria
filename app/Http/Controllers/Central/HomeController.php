<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Repositories\TenantRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected UserRepo $user, protected TenantRepo $tenant) {}

    public function index()
    {
        return redirect()->route('central_dashboard');
    }

    public function dashboard(Request $request)
    {
        $data['tenants'] = $this->tenant->getAllTenants();
        $data['users'] = $this->user->getAllUsers();

        return view('pages.central.dashboard', $data);
    }
}
