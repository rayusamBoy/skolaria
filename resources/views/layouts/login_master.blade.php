<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" {{ request()->routeIs('login', 'password.*', 'two_factor.*') ? 'force-dark-theme' : '' }}>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Rashidi Said (rayusam)">

    @includeUnless(!Qs::googleAnalyticsSetUpOkAndEnabled(), 'partials.js.google_tag')

    <title>@yield('page_title') &bull; {{ Qs::getStringAbbreviation(config('app.name')) }}</title>

    @laravelPWA

    @include('partials.login.inc_top')
</head>

<body>
    @include('partials.login.header')
    @include('partials.login.exam_announce')
    @include('pages.modals.admin')

    @yield('content')

    @include('partials.login.footer')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize uniform
            $('.form-input-styled').uniform();
        });

    </script>
</body>

</html>
