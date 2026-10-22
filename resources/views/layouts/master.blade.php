<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta id="csrf-token" name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="author" content="Rashidi Said (rayusam)" />

    <meta name="session-lifetime" content="{{ ((int) config('session.lifetime')) * 60 }}" />
    <meta name="session-expires-at" content="{{ $session_expires_at ?? '' }}" />
    <meta name="session-remember" content="{{ ($session_remember ?? false) ? '1' : '0' }}" />
    <meta name="session-ping-url" content="{{ route('session.heartbeat') }}" />
    <meta name="session-status-url" content="{{ route('session.status') }}" />
    <meta name="session-expired-url" content="{{ route('session.expired') }}" />
    <meta name="auth-user-id" content="{{ auth()->id() }}" />
    <meta name="login-url" content="{{ route('login') }}" />
    <meta name="logout-url" content="{{ route('logout') }}" />
    <meta name="auth-token" content="{{ hash_hmac('sha256', (string) auth()->id(), config('app.key')) }}" />
    <meta name="allow-offline" content="{{ (int) (auth()->user()->preferences['allow_offline_access'] ?? 0) }}" />
    <meta name="purge-offline-caches" content="{{ session('purge_offline_caches') ? '1' : '0' }}" />

    {{-- BF cache prevention — only when offline access is disabled --}}
    @if (($user_preferences['allow_offline_access'] ?? 0) == 0)
        <script>
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) window.location.reload();
            });
        </script>
    @endif

    @includeUnless(! Qs::googleAnalyticsSetUpOkAndEnabled(), 'partials.js.google_tag')
    <title>
        @yield('page_title')
        &bull; {{ config('app.name') }}
    </title>
    @laravelPWA
    @include('partials.inc_top')
    @stack('css')
</head>

<body class="{{ in_array(request()->route()->getName(), ['payments.invoice', 'marks.tabulation', 'marks.manage', 'assessments.manage', 'assessments.index', 'assessments.show', 'assessments.progressive', 'assessments.sheets', 'marks.show', 'ttr.manage', 'ttr.show', 'bin', 'events.manage', 'events.edit', 'query_builder.index', 'query_builder.select', 'notices.index', 'notices.edit', 'users.index', 'logs.index', 'students.promotion_manage', 'tenants.index', 'settings.index', 'settings_non_tenancy.index', 'analytics.index', 'analytics.fetch_data', 'tickets.reply', 'tickets.index_central', 'tickets.answer']) || $user_preferences['always_minimize_sidebar'] == 1 ? 'sidebar-xs' : '' }}">
    {{-- Block UI -- Loading page indicator; Will be removed by js script on page load; Copied from Block UI interface implementation --}}
    <div class="blockUI blockOverlay custom"></div>
    <div class="blockUI blockMsg blockPage custom">
        <div class="d-inline-flex dot-carousel mt-1 mr-3"></div>
        Loading. Please wait...
    </div>

    @include('partials.top_menu')
    <div class="page-content">
        @include('partials.menu')
        <div class="content-wrapper fl-scrolls-hoverable" data-fl-scrolls>
            @include('partials.header')

            <div class="content">
                {{-- Error Alert Area --}}
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible alert-styled-left">
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>

                        @foreach ($errors->all() as $er)
                            <span><i class="material-symbols-rounded pb-2px">hdr_strong</i> {{ $er }}</span>
                            <br />
                        @endforeach
                    </div>
                @endif

                <div class="display-none" id="ajax-alert"></div>

                @yield('content')
            </div>
        </div>
    </div>

    {{-- Go to Bottom Button --}}
    <div class="go-to-bottom">
        <button type="button" class="material-symbols-rounded btn btn-to-bottom float-right" title="Go to Bottom">
            arrow_downward
        </button>
    </div>
    {{-- Notification Sounds --}}
    <span
        class="d-none"
        id="notification-sounds"
        data-allow_system_sounds="{{ $user_preferences['allow_system_sounds'] }}"
        data-base_url="{{ asset('global_assets/sounds') }}"
    ></span>
    {{-- Offline Indicator --}}
    <div id="offline-alert">Application Offline</div>

    @include('pages.modals.session_expiry_modal')
    @include('partials.inc_bottom')

    @stack('scripts')
</body>
</html>
