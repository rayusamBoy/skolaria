<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>
        Progressive Sheet &bull; {{ $my_class->name }}
        @if (isset($section)) {{ $section->name }} @endif
        - {{ $ex->name }} ({{ $year }})
    </title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/print_assessments.css') }}" />
    @laravelPWA
</head>
<body>
    <div class="position-relative container">
        {{-- Background Logo --}}
        <div class="text-center">
            <img src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('key', 'logo')) }}" />
        </div>
        {{-- Headers --}}
        <h2 class="text-center">{{ $settings->firstWhere('key', 'system_name')->value }}</h2>
        <h3 class="text-center">
            PROGRESSIVE SHEET FOR {{ strtoupper($my_class->name) }}
            @if (isset($section)) {{ strtoupper($section->name) }} @endif
            - {{ strtoupper($ex->name) }} ({{ strtoupper(Number::spellOrdinal($asr->first()->exam->term)) }} TERM, {{ $year }})
        </h3>
        {{-- Print Area --}}
        <div id="print">
            <table class="table">
                <thead class="bg-light">
                    <tr>
                        <th class="font-weight-bold">#</th>
                        <th class="font-weight-bold">STUDENT NAME</th>
                        <th class="font-weight-bold text-vertical">Gender</th>
                        @foreach ($subjects as $sub)
                            <th class="font-weight-bold text-vertical" title="{{ $sub->name }}" rowspan="2">
                                {{ ucwords($sub->slug) }}
                            </th>
                            <th class="font-weight-bold text-vertical" title="Grade">Grade</th>
                            <th class="font-weight-bold text-vertical" title="Position">Pos</th>
                        @endforeach
                        <th class="font-weight-bold text-vertical text-darkred">Total</th>
                        <th class="font-weight-bold text-vertical text-darkblue">Average</th>
                        <th class="font-weight-bold text-vertical text-orangered">Grade</th>
                        <th class="font-weight-bold text-vertical text-darkgreen">Pos</th>
                    </tr>
                </thead>
                <tbody>
                    @include('pages.assessments.progressive.partials.table_body')
                </tbody>
            </table>

            <div>
                <small class="float-left"
                    ><em
                        >Retrieved by: {{ auth()->user()->name }} ({{ auth()->user()->roles->pluck('name')->join(', ') }})</em
                    ></small>
                <small class="float-right"><em>Printed on: {{ date('D\, j F\, Y \a\t H:i:s') }}</em></small>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
