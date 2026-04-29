<html>

<head>
    <title>Student Assessmentsheet (Minimal) - {{ $sr->user->name }}</title>

    @laravelPWA
</head>

<body>
    {{--Background Logo--}}

    <div style="position: relative;  text-align: center; ">
        <img src="{{ Usr::getTenancyAwareImgOrDefaultBgUrl($settings->firstWhere('type', 'logo')) }}" style="max-width: 500px; max-height:600px; margin-top: 60px; position:absolute ; opacity: 0.1; margin-left: auto;margin-right: auto; left: 0; right: 0;" />
    </div>

    <div style="margin: 2em;">
        {{--Headers--}}
        <div>
            <h3 style="text-align: center;"><strong>ASSESSMENTS MARKS</strong></h3>
            <div>
                <h3 style="float: left;"><strong>{{ strtoupper($sr->my_class->name) }}</strong></h3>
                <h3 style="float: right;"><strong>YEAR: {{ $asr->exam->year }}</strong></h3>
            </div>

            <h3 style="text-align: center;"><strong> {{ Number::spellOrdinal($asr->exam->term) }} TERM REPORT</strong></h3>
            <div style="display: flow-root;">
                <em style="float: left;">Term Begins: <span style="text-decoration: underline;">{{ $settings->firstWhere('type', 'term_begins')->description ?? '' }}</span></em>
                <em style="float: right;">Term Ends: <span style="text-decoration: underline;">{{ $settings->firstWhere('type', 'term_ends')->description ?? '' }}</span></em>
            </div>
        </div>
        {{--Body--}}
        <table border="1" style="table-layout: fixed; border-collapse: collapse; margin-top: 0.5em; text-align: center">
            <tbody>
                <tr>
                    <td style="width: 1%;">Subject</td>

                    @foreach($subjects as $sub)
                    <td>{{ $sub->slug }}.</td>
                    @endforeach

                    <td>Total</td>
                    <td>Average</td>
                </tr>
                <tr>
                    <td style="white-space: nowrap;">Maximum Marks</td>

                    @foreach($subjects as $sub)
                    <td>100</td>
                    @endforeach

                    <td>{{ $total = count($subjects) * 100 }}</td>
                    <td>{{ $total / count($subjects) }}</td>
                </tr>
                <tr>
                    <td>Marks Obtained</td>

                    @foreach($subjects as $sub)
                    {{--If the record for a particular subject exists--}}
                    @if($assessments_records->where('subject_id', $sub->id)->first())
                    @foreach($assessments_records->where('subject_id', $sub->id) as $as)
                    <td>{{ data_get($as, $tex, '—') }}</td>
                    @endforeach
                    {{--If not show 'dash'--}}
                    @else
                    <td>—</td>
                    @endif
                    @endforeach

                    <td>{{ $assessments_records->first()->total }}</td>
                    <td>{{ $average = $assessments_records->first()->ave }}</td>
                </tr>
                <tr>
                    <td>Assessment</td>

                    @foreach($subjects as $sub)
                    @if($assessments_records->where('subject_id', $sub->id)->first())
                    @foreach($assessments_records->where('subject_id', $sub->id) as $as)
                    <td>{{ $as->grade->name ?? '—' }}</td>
                    @endforeach

                    @else
                    <td>&mdash;</td>
                    @endif
                    @endforeach

                    <td></td>
                    <td>{{ Mk::getGrade($average) }}</td>
                </tr>
                <tr>
                    <td>Position</td>

                    @foreach($subjects as $sub)
                    @if($assessments_records->where('subject_id', $sub->id)->first())
                    @foreach($assessments_records->where('subject_id', $sub->id) as $as)
                    <td>{{ $as->sub_pos ?? "—" }}</td>
                    @endforeach
                    @else
                    <td>—</td>
                    @endif
                    @endforeach

                    <td></td>
                    <td>{{ $assessments_records->first()->pos }} / {{ Mk::getSectionCount($asr->exam->id, $my_class->id, $sr->section->id) }}</td>
                </tr>
            </tbody>
        </table>
        {{--Footers--}}
        <table style="margin-top: 1em; width: 100%;">
            <tbody>
                <tr>
                    <td rowspan="6">
                        <h3>REMARKS</h3>
                    </td>
                </tr>
                <tr>
                    <td>Time Absent: {{ str_repeat("_", 32) }}</td>
                    <td style="float: right;">Class Teacher's Sign: {{ str_repeat("_", 10) }}</td>
                </tr>
                <tr>
                    <td>Time Late: {{ str_repeat("_", 34) }}</td>
                    <td style="float: right;">Head Teacher's Sign: {{ str_repeat("_", 10) }}</td>
                </tr>
                <tr>
                    <td>National Building Activities: {{ str_repeat("_", 20) }}</td>
                    <td style="float: right;">Parent/Gurdian Sign: {{ str_repeat("_", 10) }}</td>
                </tr>
                <tr>
                    <td>Cooperation with Others: {{ str_repeat("_", 22) }}</td>
                </tr>
                <tr>
                    <td>Class Master's Remarks: {{ str_repeat("_", 23) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        window.addEventListener('load', function() {
            window.print();
        });

    </script>
</body>
