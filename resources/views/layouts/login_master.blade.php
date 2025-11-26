<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="Rashidi Said (rayusam)">

    @php
    $settings = Qs::getSettings();
    $colors = $settings->firstWhere('type', 'login_and_related_pgs_txts_and_bg_colors')->description;
        if ($colors !== null) {
            $colors_exploaded = explode(Qs::getDelimiter(), $colors);
            $texts_color = $colors_exploaded[0];
            $bg_color = $colors_exploaded[1];
        }
    @endphp

    @if (Qs::googleAnalyticsSetUpOkAndEnabled())
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $settings->firstWhere("type", "google_analytic_tag_id")->description }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];

            function gtag() {
                dataLayer.push(arguments);
            }
            gtag('js', new Date());
            gtag('config', '{{ $settings->firstWhere("type", "google_analytic_tag_id")->description }}');
        </script>
    @endif

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
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize uniform
            $('.form-input-styled').uniform();
        }); 
    </script>
</body>

</html>
