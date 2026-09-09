<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>
        Tabulation Sheet - {{ strtoupper($settings->firstWhere('key', 'system_title')->value) }} - {{ $my_class->name }}
        @if (isset($section)) {{ $section->name }} @endif
        {{ $ex->name }} ({{ $year }})
    </title>
    <link rel="stylesheet" href="{{ asset('assets/css/print_tabulation.css') }}" />

    @laravelPWA
</head>

<body>
    <div class="hide" style="margin-bottom: 20px">
        <button class="btn" onclick="window.print()">Print</button>
        <div class="float-right">
            <select id="hide-select" onchange="hideSelected(this.value)">
                <option disabled selected>Hide...</option>
            </select>
            <select id="unhide-select" onchange="unhideSelected(this.value)">
                <option disabled selected>Unhide...</option>
            </select>
        </div>
    </div>

    <div id="printable">
        @include('pages.marks.tabulation.partials.header')
        @include('pages.marks.tabulation.partials.summaries')
        @include('pages.marks.tabulation.partials.table')
        <div style="page-break-after: always"></div>
        @include('pages.marks.tabulation.partials.performance_summary')
        <p class="copyright mt-4 text-center">
            {{ date('Y') }}. {{ ucwords(strtolower($settings->firstWhere('key', 'system_name')->value)) }}. All rights
            reserved.
        </p>
    </div>

    {{-- JavaScript --}}
    <script>
        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        (function () {
            const toggleables = document.querySelectorAll('[data-toggle-target]');
            const hideSelect = document.getElementById('hide-select');
            const unhideSelect = document.getElementById('unhide-select');

            const items = new Map();

            toggleables.forEach((el) => {
                const selector = el.dataset.toggleTarget;
                if (!selector) return;

                let label = el.getAttribute('id');
                label = label.replace('-', ' ', label);
                label = capitalize(label);

                items.set(selector, label);
            });

            const sorted = Array.from(items.entries()).sort((a, b) => a[1].localeCompare(b[1]));

            sorted.forEach(([selector, label]) => {
                hideSelect.add(new Option(label, selector));
                unhideSelect.add(new Option(label, selector));
            });

            window.hideSelected = (selector) =>
                document.querySelectorAll(selector).forEach((el) => (el.style.display = 'none'));
            window.unhideSelected = (selector) =>
                document.querySelectorAll(selector).forEach((el) => (el.style.display = ''));
        })();
    </script>
</body>
</html>
