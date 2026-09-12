<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    {{ request()->routeIs('login', 'password.*', 'two_factor.*') ? 'force-dark-theme' : '' }}
>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="author" content="Rashidi Said (rayusam)" />

    @includeUnless(! Qs::googleAnalyticsSetUpOkAndEnabled(), 'partials.js.google_tag')

    <title>
        @yield('page_title')
        &bull; {{ Qs::getStringAbbreviation(config('app.name')) }}
    </title>

    @laravelPWA

    @include('partials.login.inc_top')
</head>

<body>
    @include('partials.login.header')
    @include('partials.login.exam_announce')
    @include('pages.modals.admin')

    @yield('content')

    @include('partials.login.footer')

    {{-- Theme JS files --}}
    <script src="{{ asset('global_assets/js/plugins/extensions/jquery_ui/interactions.min.js') }} "></script>
    <script src="{{ asset('global_assets/js/plugins/forms/selects/select2.min.js') }} "></script>
    {{-- Forms --}}
    <script src="{{ asset('global_assets/js/plugins/forms/wizards/steps.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/forms/styling/uniform.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/forms/validation/validate.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/forms/wizards/form_wizard.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/forms/selects/form_select2.js') }}"></script>

    <script src="{{ asset('global_assets/js/plugins/uploaders/uploader_bootstrap.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/ui/autosize.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/loaders/blockui.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/ui/moment/moment.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/pickers/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('global_assets/js/plugins/pickers/pickadate/legacy.js') }}"></script>
    <script src="{{ asset('assets/js/custom-min.js') }}"></script>
    <script>
        const get_state_url = '{{ route("ajax.get_state", [":id"]) }}';
        const get_lga_url = '{{ route("ajax.get_lga", [":id"]) }}';
    </script>
</body>
</html>
