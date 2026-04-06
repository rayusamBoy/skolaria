<!DOCTYPE html>
<html>
    
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SMS') }} &bull; @yield('title')</title>
    <!-- Styles -->
    <style>
        svg {
            width: 25vh;
            height: 5vw;
            margin: auto
        }

    </style>

    @stack('css')

    @laravelPWA

    @include('errors.inc_top')
</head>

<body>
    <div class="navbar navbar-dark login">
        @if(session('tenant_account_not_active'))
        <h6 class="text-bold text-info text-color-custom mt-auto mb-auto">{{ session('tenant_account_not_active') }}</h6>
        @else
        <h6 class="mt-auto mb-auto text-color-custom">@yield('title')</h6>
        @endif

        <a href="{{ route('home') }}" class="navbar-nav-link d-flex text-color-custom float-right">
            <span class="mr-1">{{ "Home" }}</span>
            <i class="material-symbols-rounded">home</i>
        </a>
    </div>

    @yield('content')

    {{-- Put error code into session for other uses ie., to activate other actions like account recover for 2fa 'see verify.blade.php in view/auth/...' --}}
    @php session()->put('error_code', $error_code ?? null) @endphp

    <script type="module">
        import { animate, stagger, splitText, svg, eases, cubicBezier, spring, irregular, steps } from 'https://cdn.jsdelivr.net/npm/animejs/dist/bundles/anime.esm.min.js';

        // Returns a random easing, sometimes as a string, sometimes as a function
        function getRandomEaseMixed() {
            const easeNames = [
                'inOutQuad', 'inQuad', 'outQuad',
                'inCubic', 'outCubic', 'inOutCubic',
                'inSine', 'outSine', 'inOutSine',
                'inExpo', 'outExpo', 'inOutExpo',
                'inBack', 'outBack', 'inOutBack',
                'inBounce', 'outBounce', 'inOutBounce',
                'inCirc', 'outCirc', 'inOutCirc'
            ];

            const parametric = [
                `easeInElastic(${Math.random() * 5 + 1}, ${Math.random() * 2 + 0.2})`,
                `easeOutElastic(${Math.random() * 5 + 1}, ${Math.random() * 2 + 0.2})`,
                `easeInOutElastic(${Math.random() * 5 + 1}, ${Math.random() * 2 + 0.2})`
            ];

            const funcEases = [
                eases.inQuad,
                eases.outQuad,
                eases.inOutCubic,
                cubicBezier(Math.random(), Math.random(), Math.random(), Math.random()),
                spring({
                    mass: Math.random() * 2 + 0.5,
                    stiffness: Math.random() * 200 + 50,
                    damping: Math.random() * 20 + 5,
                    velocity: 0
                }),
                irregular(Math.round(Math.random() * 20 + 5), Math.random() * 1.5 + 0.5),
                steps(Math.round(Math.random() * 10 + 2))
            ];

            const all = [
                ...easeNames,
                ...parametric,
                ...funcEases
            ];

            const choice = all[Math.floor(Math.random() * all.length)];
            return choice;
        }

        // Returns a random draw keyframes array
        function getRandomDraw() {
            // possible draw states
            const states = [
                '0 0',
                '0 1',
                '1 1',
                '0 .5',
                '.5 1',
                '.25 .75'
            ];

            // pick three random states for a 3-step animation
            const pick = () => states[Math.floor(Math.random() * states.length)];
            return [ pick(), pick(), pick() ];
        }

        animate(svg.createDrawable('svg'), {
            draw: getRandomDraw(),
            ease: getRandomEaseMixed(),
            duration: 2000,
            delay: stagger(100),
            alternate: true,
            loop: true
        });
    </script>
</body>

</html>
