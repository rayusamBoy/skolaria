@php
    $headings_count = count($heading_names);
    // Split the header row into 4 roughly equal columns (for CLASS, YEAR, NATIONALITY, STATE)
    $base_span = (int) floor($headings_count / 4);
    $remainder = $headings_count - ($base_span * 3);
    // $remainder will be used for the last column (STATE) to ensure total equals $headings_count
@endphp

<table>
    <thead style="text-align: center">
        {{-- System name row --}}
        <tr>
            <th style="text-align: center" colspan="{{ $headings_count }}">
                <strong>{{ Qs::getSystemName() }}</strong>
            </th>
        </tr>

        {{-- Template title row --}}
        <tr>
            <th style="text-align: center" colspan="{{ $headings_count }}">
                <strong>STUDENTS BATCH ADD TEMPLATE</strong>
            </th>
        </tr>

        {{-- Meta information row (spread across 4 columns) --}}
        <tr>
            <th style="text-align: center" colspan="{{ $base_span }}">
                <strong>CLASS: {{ strtoupper($class->name) }}</strong>
            </th>
            <th style="text-align: center" colspan="{{ $base_span }}"><strong>YEAR: {{ $year }}</strong></th>
            <th style="text-align: center" colspan="{{ $base_span }}">
                <strong>NATIONALITY: {{ strtoupper($nationality->name) }}</strong>
            </th>
            <th style="text-align: center" colspan="{{ $remainder }}">
                <strong>STATE: {{ strtoupper($state->name) }}</strong>
            </th>
        </tr>

        {{-- Hidden database parameter reference row --}}
        <tr>
            @foreach ($heading_names as $human_name => $param)
                <th style="text-align: center"><strong>{{ $param }}</strong></th>
            @endforeach
        </tr>

        {{-- Visible human‑readable row (required fields marked with *) --}}
        <tr>
            @foreach ($heading_names as $human_name => $param)
                <th style="text-align: center">
                    <strong>
                        {{ in_array($param, $parameters_with_rule_required) ? "$human_name *" : $human_name }}
                    </strong>
                </th>
            @endforeach
        </tr>
    </thead>

    <tbody>
        @for ($row = 1; $row <= $end_data_row; $row++)
            <tr>
                @foreach ($heading_names as $human_name => $param)
                    @if (in_array($param, $parameters_columns_to_hide_and_populate))
                        {{-- Auto‑populated value (e.g. class ID, state ID) --}}
                        <td style="text-align: center">{{ ${$param} }}</td>
                    @else
                        <td></td>
                    @endif
                @endforeach
            </tr>
        @endfor
    </tbody>
</table>
