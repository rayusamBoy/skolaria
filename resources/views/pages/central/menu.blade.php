{{-- Dashboard --}}
<li class="nav-item">
    <a href="{{ route('central_dashboard') }}" class="nav-link {{ request()->routeIs('central_dashboard', 'central_home') ? 'active' : '' }}">
        <i class="material-symbols-rounded">{{ request()->routeIs('central_dashboard') ? 'dashboard' : 'home' }}</i>
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

{{-- Users & Roles --}}
@canany([
...array_column($central_permissions['central_users'], 'permission'),
...array_column($central_permissions['roles'], 'permission')
])
<li class="nav-item nav-item-submenu {{ request()->routeIs('central_users.*','roles.*') ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="#" class="nav-link">
        <i class="material-symbols-rounded">group</i>
        <span>Users & Roles</span>
    </a>
    <ul class="nav nav-group-sub" data-submenu-title="Users & Roles">
        @canany(array_column($central_permissions['central_users'], 'permission'))
        <li class="nav-item">
            <a href="{{ route('central_users.index') }}" class="nav-link {{ request()->routeIs('central_users.*') ? 'active' : '' }}">
                Users
            </a>
        </li>
        @endcanany
        @can($central_permissions['roles']['index']['permission'])
        <li class="nav-item">
            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                Roles
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

{{-- Tenants --}}
@canany(array_column($central_permissions['tenants'], 'permission'))
<li class="nav-item">
    <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
        <i class="material-symbols-rounded">manage_accounts</i>
        <span>Tenants</span>
    </a>
</li>
@endcanany

{{-- Support --}}
@canany(array_column($central_permissions['tickets'], 'permission'))
<li class="nav-item nav-item-submenu {{ request()->routeIs('tickets.index_central') ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="#" class="nav-link">
        <i class="material-symbols-rounded">support</i>
        <span>Support</span>
    </a>
    <ul class="nav nav-group-sub" data-submenu-title="Support">
        <li class="nav-item">
            <a href="{{ route('tickets.index_central') }}" class="nav-link {{ request()->routeIs('tickets.index_central') ? 'active' : '' }}">
                Tickets
            </a>
        </li>
    </ul>
</li>
@endcanany

{{-- Settings --}}
@can($central_permissions['settings_non_tenancy']['index']['permission'])
<li class="nav-item">
    <a href="{{ route('settings_non_tenancy.index') }}" class="nav-link {{ request()->routeIs('settings_non_tenancy.*') ? 'active' : '' }}">
        <i class="material-symbols-rounded">settings</i>
        <span>Settings</span>
    </a>
</li>
@endcan
