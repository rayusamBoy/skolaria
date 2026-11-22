<!-- Global stylesheets -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />
{{-- <link href="{{ asset('global_assets/css/icons/material/icons.css') }}" rel="stylesheet" type="text/css"> --}}
<link href="{{ asset('assets/css/bootstrap-min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/skins-min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/bootstrap_limitless-min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/layout-min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/components-min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/colors-min.css') }}" rel="stylesheet" type="text/css" />

<!-- Core JS files -->
<script src="{{ asset('global_assets/js/main/jquery.min.js') }} "></script>
<script src="{{ asset('global_assets/js/main/bootstrap.bundle.min.js') }} "></script>

<!-- Theme JS files -->
<script src="{{ asset('assets/js/app-min.js') }} "></script>
<script src="{{ asset('global_assets/js/plugins/forms/styling/uniform.min.js') }} "></script>
<script src="{{ asset('global_assets/js/plugins/scrollers/jquery.marquee.min.js') }}"></script>
<script src="{{ asset('global_assets/js/plugins/extensions/js-cookie.js') }}"></script>
<script src="{{ asset('global_assets/js/main/color-modes.js') }}"></script>

@php
    $colors = $settings->firstWhere('key', 'login_and_related_pgs_txts_and_bg_colors')?->value;
    $texts_color = 'var(--navbar-text)';
    $bg_color = 'var(--navbar-bg)';
    if ($colors !== null) {
        $colors_exploaded = explode(Qs::getDelimiter(), $colors);
        $texts_color = $colors_exploaded[0];
        $bg_color = $colors_exploaded[1];
    }
    $bg_url = Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'login_and_related_pages_bg'));
@endphp

<style>
    html,
    body {
        color-scheme: dark;
    }

    div.page-content.login-cover {
        background-image: url({{ $bg_url }});
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        background-color: {{ $bg_color }} !important;
        background-blend-mode: soft-light;
        animation-name: sides-move;
        animation-duration: 60s;
        animation-iteration-count: infinite;
    }

    div.exam-announce-wrapper {
        box-shadow:
            -4px 5px 10px inset #333333,
            4px -5px 10px inset {{ $bg_color }};
    }

    .text-color-custom {
        color: {{ $texts_color }} !important;
    }

    .navbar {
        background-color: {{ $bg_color }} !important;
    }

    .hover-effect {
        transition:
            transform 0.2s ease,
            box-shadow 0.2s ease;
    }

    .hover-effect:hover {
        transform: translateY(-3px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
