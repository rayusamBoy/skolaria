<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>
        Tabulation Sheet - {{ strtoupper($settings->firstWhere('key', 'system_title')->value) }} &middot; {{ $my_class->name }}
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
                <option value="" disabled selected>Hide...</option>
            </select>
            <select id="unhide-select" onchange="unhideSelected(this.value)">
                <option value="" disabled selected>Unhide...</option>
            </select>
        </div>
    </div>

    <div id="printable">
        @include('pages.marks.tabulation.partials.header')
        @include('pages.marks.tabulation.partials.summaries')
        @include('pages.marks.tabulation.partials.table')
        <div style="page-break-after: auto"></div>
        @include('pages.marks.tabulation.partials.performance_summary')
        <p class="copyright mt-4 text-center">
            &copy; {{ date('Y') }}. {{ ucwords(strtolower($settings->firstWhere('key', 'system_name')->value)) }}. All
            rights reserved.
        </p>
    </div>

    {{-- JavaScript --}}
    <script>
        (function () {
            const toggleables = document.querySelectorAll('[data-toggle-target]');
            const hideSelect = document.getElementById('hide-select');
            const unhideSelect = document.getElementById('unhide-select');

            // Build the unique selector → label map
            const items = new Map();
            toggleables.forEach((el) => {
                const selector = el.dataset.toggleTarget;
                if (!selector || items.has(selector)) return;

                let label = el.getAttribute('id') ?? el.dataset.label ?? selector;
                label = label.replace(/-/g, ' '); // fixed: was .replace('-', ' ', label)
                label = label.charAt(0).toUpperCase() + label.slice(1);

                items.set(selector, label);
            });

            const sorted = Array.from(items.entries()).sort((a, b) => a[1].localeCompare(b[1]));

            // Seed hide-select with every toggleable
            sorted.forEach(([selector, label]) => hideSelect.add(new Option(label, selector)));

            // Helpers
            const moveOption = (fromSelect, toSelect, selector) => {
                const option = Array.from(fromSelect.options).find((o) => o.value === selector);
                if (!option) return;

                const label = option.text;
                option.remove();

                // Insert alphabetically after the placeholder (index 0).
                const insertBefore = Array.from(toSelect.options)
                    .slice(1)
                    .find((o) => o.text.localeCompare(label) > 0);

                toSelect.add(new Option(label, selector), insertBefore ?? null);
            };

            const resetSelect = (select) => {
                select.selectedIndex = 0;
            };

            // Disable a select if only the placeholder remains.
            const syncEmptyState = () => {
                hideSelect.disabled = hideSelect.options.length <= 1;
                unhideSelect.disabled = unhideSelect.options.length <= 1;
            };

            // Public handlers
            window.hideSelected = (selector) => {
                if (!selector) return;
                document.querySelectorAll(selector).forEach((el) => (el.style.display = 'none'));
                moveOption(hideSelect, unhideSelect, selector);
                resetSelect(hideSelect);
                syncEmptyState();
            };

            window.unhideSelected = (selector) => {
                if (!selector) return;
                document.querySelectorAll(selector).forEach((el) => (el.style.display = ''));
                moveOption(unhideSelect, hideSelect, selector);
                resetSelect(unhideSelect);
                syncEmptyState();
            };

            syncEmptyState();
        })();
    </script>
</body>
</html>
