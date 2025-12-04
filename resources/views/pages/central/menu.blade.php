{{-- Dashboard --}}
<li class="nav-item">
    <a href="{{ route('central_dashboard') }}" class="nav-link {{ request()->routeIs('central_dashboard', 'central_home') ? 'active' : '' }}">
        <i class="material-symbols-rounded">
            {{ request()->routeIs('central_dashboard') ? 'dashboard' : 'home' }}
        </i>
        <span>Dashboard</span>
    </a>
</li>

{{-- My Account --}}
<li class="nav-item">
    <a href="{{ route('my_account.edit') }}" class="nav-link {{ request()->routeIs('my_account.*') ? 'active' : '' }}">
        <i class="material-symbols-rounded">account_circle</i>
        <span>My Account</span>
    </a>
</li>

{{-- Manage Users --}}
@canany(array_values($central_permissions['central_users']))
<li class="nav-item">
    <a href="{{ route('central_users.index') }}" class="nav-link {{ request()->routeIs('central_users.*') ? 'active' : '' }}">
        <i class="material-symbols-rounded">group</i>
        <span>Users</span>
    </a>
</li>
@endcanany

{{-- Manage Tenants --}}
@canany(array_values($central_permissions['tenants']))
<li class="nav-item">
    <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
        <i class="material-symbols-rounded">manage_accounts</i>
        <span>Tenants</span>
    </a>
</li>
@endcanany

{{-- Manage Settings --}}
@canany([...array_values($central_permissions['settings_non_tenancy']), ...array_values($central_permissions['roles'])])
<li class="nav-item nav-item-submenu {{ request()->routeIs('settings_non_tenancy.*', 'permissions.*', 'roles.*') ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="javascript:;" class="nav-link">
        <i class="material-symbols-rounded">settings</i>
        <span>Settings</span>
    </a>

    <ul class="nav nav-group-sub" data-submenu-title="Settings">
        {{-- App --}}
        <li class="nav-item">
            <a href="{{ route('settings_non_tenancy.index') }}" class="nav-link {{ request()->routeIs('settings_non_tenancy.index') ? 'active' : '' }}">
                App
            </a>
        </li>

        {{-- Roles --}}
        <li class="nav-item">
            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                Roles
            </a>
        </li>
    </ul>
</li>
@endcanany

{{-- Support --}}
@canany(array_values($central_permissions['tickets']))
<li class="nav-item nav-item-submenu {{ request()->routeIs('tickets.index_central') ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="javascript:;" class="nav-link">
        <i class="material-symbols-rounded">support</i>
        <span>Support</span>
    </a>

    <ul class="nav nav-group-sub" data-submenu-title="Support">
        {{-- Tickets --}}
        <li class="nav-item">
            <a href="{{ route('tickets.index_central') }}" class="nav-link {{ request()->routeIs('tickets.index_central') ? 'active' : '' }}">
                Tickets
            </a>
        </li>
    </ul>
</li>
@endcanany
