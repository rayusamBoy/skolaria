<div class="navbar navbar-expand-md">
    <div class="mt-2 mr-md-5">
        <a class="d-inline-block">
            <h4 class="text-bold d-none d-md-block">{{ Qs::getSystemName() }}</h4>
            <h4 class="text-bold d-block d-md-none">{{ Qs::getStringAbbreviation() }}</h4>
        </a>
    </div>

    <div class="d-flex d-md-none">
        <span class="navbar-toggler">
            @include('pages.notifications.badge')
        </span>

        @if(Usr::tenancyInitilized())
        @can($tenant_permissions['messages']['index']['permission'])
        <a class="navbar-toggler text-default" title="Messages" href="{{ route('messages.index') }}">
            @include('messenger.unread-count')
        </a>
        @endcan
        @else
        @can($tenant_permissions['tickets']['index']['permission'])
        <div class="navbar-toggler">
            @include('pages.central.support.tickets.badge')
        </div>
        @endcan
        @endif

        <a href="javascript:;" class="navbar-toggler text-default refresh mobile">
            <i class="material-symbols-rounded mt-auto mb-auto">refresh</i>
        </a>
        <a href="javascript:;" class="navbar-toggler text-default border-0 color-mode-toggle" title="Switch theme" data-theme="light">
            <i class="material-symbols-rounded mr-1 mt-auto mb-auto">light_mode</i>
        </a>
        <a class="navbar-toggler text-default" type="button" data-toggle="collapse" data-target="#navbar-mobile">
            <i class="material-symbols-rounded align-middle">person_alert</i>
        </a>
        <a class="navbar-toggler sidebar-mobile-main-toggle text-default" type="button">
            <i class="material-symbols-rounded align-middle">menu</i>
        </a>
    </div>

    <div class="collapse navbar-collapse" id="navbar-mobile">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="javascript:;" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block {{ $user_preferences['always_minimize_sidebar'] == 1 ? 'disabled pointer-events-none' : '' }}">
                    <i class="material-symbols-rounded align-middle">menu</i>
                </a>
            </li>

            <li class="d-none d-md-block full-screen-handle">
                <a href="javascript:;" title="Toggle Full Screen" class="navbar-nav-link">
                    <i class="material-symbols-rounded align-middle">fullscreen</i>
                </a>
            </li>
        </ul>

        <span class="navbar-text ml-md-3 mr-md-auto"></span>

        <a href="javascript:;" class="navbar-nav-link d-none d-md-block refresh">
            <i class="material-symbols-rounded mt-auto mb-auto mr-1">refresh</i>
            <span>Refresh</span>
        </a>

        <a href="javascript:;" class="navbar-nav-link d-none d-md-block border-0 color-mode-toggle" title="Switch theme" data-theme="light">
            <i class="material-symbols-rounded mr-1 mt-auto mb-auto">light_mode</i>
        </a>

        <ul class="navbar-nav">
            <li class="nav-item dropdown dropdown-user">
                <a href="javascript:;" class="navbar-nav-link" data-toggle="dropdown">
                    <img src="{{ Usr::getTenancyAwarePhoto(auth()->user()->photo) }}" class="rounded-circle" style="width:38px;height:38px" alt="photo">
                    <span>{{ auth()->user()->name }}</span>
                    <i class="material-symbols-rounded ml-1">keyboard_arrow_down</i>
                </a>

                <div class="dropdown-menu dropdown-menu-right">

                    {{-- Profile --}}
                    <a href="{{ auth()->user()->hasRole(config('roles.default_roles.tenant.student'))
                                  ? route('students.show',  Qs::hash(Qs::findStudentRecord(auth()->id())->id))
                                  : route(Usr::tenancyInitilized() ? 'users.show' : 'central_users.show', Qs::hash(auth()->id())) }}" class="dropdown-item {{ request()->routeIs('students.show', 'users.show', 'central_users.show') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">person</i>My Profile
                    </a>

                    @unless(Usr::tenancyInitilized())
                    <div class="dropdown-divider"></div>
                    {{-- View System Log --}}
                    @if(array_intersect(auth()->user()->roles()->pluck('name')->toArray(), array_values(config('roles.root_roles.central'))) > 0)
                    <a href="/log-viewer" class="dropdown-item">
                        <i class="material-symbols-rounded">description</i>View System Log
                    </a>
                    @endif
                    {{-- Artisan Commands --}}
                    @canany(array_column($central_permissions['artisan_commands'], 'permission'))
                    <a href="{{ route('artisan_commands.index') }}" class="dropdown-item {{ request()->routeIs('artisan_commands.index') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">terminal</i>Artisan Commands
                    </a>
                    @endcanany
                    {{-- Central Activity Log --}}
                    @canany(array_column($central_permissions['central_logs'], 'permission'))
                    <a href="{{ route('central_logs.index') }}" class="dropdown-item {{ request()->routeIs('central_logs.index') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">browse_activity</i>Activity Log
                    </a>
                    @endcanany

                    @else
                    {{-- Tenant Activity Log --}}
                    @canany(array_column($tenant_permissions['logs'], 'permission'))
                    <a href="{{ route('logs.index') }}" class="dropdown-item {{ request()->routeIs('logs.index') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">browse_activity</i>Activity Log
                    </a>
                    @endcanany
                    @endunless

                    <div class="dropdown-divider"></div>
                    {{-- Account Settings --}}
                    <a href="{{ route('my_account.edit') }}" class="dropdown-item {{ request()->routeIs('my_account.edit') ? 'active' : '' }}">
                        <i class="material-symbols-rounded">manage_accounts</i>Account Settings
                    </a>
                    {{-- Account Security --}}
                    <a href="{{ route('account_security.index') }}" class="dropdown-item {{ request()->routeIs('account_security.index') ? 'active' : '' }}"><i class="material-symbols-rounded">passkey</i> Account Security</a>
                    {{-- Accessibilty --}}
                    <a href="javascript:;" class="dropdown-item"><i class="material-symbols-rounded">accessibility_new</i> Accessibility</a>
                    {{-- Logout --}}
                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item text-warning">
                        <i class="material-symbols-rounded">logout</i>Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>
