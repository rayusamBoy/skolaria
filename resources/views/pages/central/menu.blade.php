{{-- Dashboard --}}
<li class="nav-item">
    <a
        href="{{ route('central_dashboard') }}"
        class="nav-link {{ request()->routeIs('central_dashboard', 'central_home') ? 'active' : '' }}"
    >
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
    'central.users.view',
    'roles.view',
])
    <li class="nav-item nav-item-submenu {{ request()->routeIs('central_users.*','roles.*') ? 'nav-item-expanded nav-item-open' : '' }}">
        <a href="#" class="nav-link">
            <i class="material-symbols-rounded">group</i>
            <span>Users & Roles</span>
        </a>
        <ul class="nav nav-group-sub" data-submenu-title="Users & Roles">
            @can('central.users.view')
                <li class="nav-item">
                    <a
                        href="{{ route('central_users.index') }}"
                        class="nav-link {{ request()->routeIs('central_users.*') ? 'active' : '' }}"
                    >
                        Users
                    </a>
                </li>
            @endcan
            @can('central.roles.view')
                <li class="nav-item">
                    <a
                        href="{{ route('roles.index') }}"
                        class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}"
                    >
                        Roles
                    </a>
                </li>
            @endcan
        </ul>
    </li>
@endcanany

{{-- Tenants --}}
@can('central.tenants.view')
    <li class="nav-item">
        <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
            <i class="material-symbols-rounded">manage_accounts</i>
            <span>Tenants</span>
        </a>
    </li>
@endcan

{{-- Support --}}
@can(['tickets.index_central'])
    <li class="nav-item nav-item-submenu {{ request()->routeIs('tickets.index_central') ? 'nav-item-expanded nav-item-open' : '' }}">
        <a href="#" class="nav-link">
            <i class="material-symbols-rounded">support</i>
            <span>Support</span>
        </a>
        <ul class="nav nav-group-sub" data-submenu-title="Support">
            <li class="nav-item">
                <a
                    href="{{ route('tickets.index_central') }}"
                    class="nav-link {{ request()->routeIs('tickets.index_central') ? 'active' : '' }}"
                >
                    Tickets
                </a>
            </li>
        </ul>
    </li>
@endcan

{{-- Settings --}}
@can('central.settings.view')
    <li class="nav-item">
        <a
            href="{{ route('settings_non_tenancy.index') }}"
            class="nav-link {{ request()->routeIs('settings_non_tenancy.*') ? 'active' : '' }}"
        >
            <i class="material-symbols-rounded">settings</i>
            <span>Settings</span>
        </a>
    </li>
@endcan
