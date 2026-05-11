{{--NAME , CLASS AND OTHER INFO --}}
<table style="width:100%; border-collapse:collapse; ">
    <tbody>
        <tr>
            <td><strong>NAME:</strong> {{ strtoupper($sr->user->name) }}</td>
            <td><strong>ADM NO:</strong> {{ $sr->admission_number }}</td>
            {{--<td><strong>HOUSE:</strong> {{ strtoupper($sr->house) }}</td>--}}
            <td><strong>CLASS:</strong> {{ strtoupper($my_class->name) }}</td>
        </tr>
        <tr>
            <td><strong>ASSESSMENTS MARKS FOR </strong>{{ Number::spellOrdinal($asr->exam->term) }} TERM</td>
            <td><strong>EXAM: </strong>{{ strtoupper($asr->exam->name) }} ({{ $asr->exam->year }})</td>
            <td><strong>AGE: </strong>{{ $sr->age ?: ($sr->user->date_of_birth ? date_diff(date_create($sr->user->date_of_birth), date_create('now'))->y : '—') }}</td>
        </tr>
    </tbody>
</table>

{{--Exam Table--}}
<table style="width:100%; border-collapse:collapse; border: 1px solid #000; margin: 10px auto;" border="1">
    <thead>
        <tr>
            <th rowspan="2">SUBJECTS</th>
            {{--Course Work--}}
            <th rowspan="2">CW<br />(10)</th>
            {{--Home Work--}}
            <th rowspan="2">HW<br />(10)</th>
            {{--Topic Test--}}
            <th rowspan="2">TT<br />(20)</th>
            <th rowspan="2">EXAMS<br />(60)</th>
            <th rowspan="2">TOTAL<br />(100)</th>
            <th rowspan="2">GRADE</th>
            <th rowspan="2">SUBJECT <br> POSITION</th>
            <th rowspan="2">REMARKS</th>
        </tr>
    </thead>
    <tbody>
        @foreach($subjects as $sub)
        <tr>
            <td style="font-weight: bold">{{ $sub->name }}</td>
            @foreach($assessments_records->where('subject_id', $sub->id)->where('exam_id', $asr->exam->id) as $as)
            {{--Course Work--}}
            <td>@php $cw_avg = Mk::getAverage($as->cw1 + $as->cw2 + $as->cw3 + $as->cw4 + $as->cw5 + $as->cw6 + $as->cw7 + $as->cw8 + $as->cw9 + $as->cw10, 10) @endphp {{ $cw_avg != 0 ? $cw_avg : '—' }}</td>
            {{--Home Work--}}
            <td>@php $hw_avg = Mk::getAverage($as->hw1 + $as->hw2 + $as->hw3 + $as->hw4 +$as->hw5, 5) @endphp {{ $hw_avg != 0 ? $hw_avg : '—' }}</td>
            {{--Topic Test--}}
            <td>@php $tt_avg = Mk::getAverage($as->tt1 + $as->tt2 + $as->tt2, 3) @endphp {{ $tt_avg != 0 ? $tt_avg : '—'  }}</td>
            <td>{{ $as->evaluated_exam_mark ?? '—' }}</td>
            <td>{{ data_get($as, $tex, '—') }}</td>
            <td>{{ $as->grade?->name ?? '—' }}</td>
            <td>{!! $as->grade ? Mk::getOrdinalSuffix($as->subject_position) : '—' !!}</td>
            <td>{{ $as->grade?->remarks ?? '—' }}</td>
            @endforeach
        </tr>
        @endforeach
        <tr>
            <td colspan="3"><strong>TOTAL SCORES OBTAINED: </strong> {{ $asr->total ?? '—' }}</td>
            <td colspan="3"><strong>FINAL AVERAGE: </strong> {{ $asr->average ?? '—' }}</td>
            <td colspan="3"><strong>CLASS AVERAGE: </strong> {{ $asr->class_average ?? '—' }}</td>
        </tr>
        <tr>
            <td colspan="3"><strong>FINAL POSITION: </strong>{{ $asr->section_position }} out-of {{ Mk::getSectionCount($asr->exam->id, $my_class->id, $sr->section->id) }}</td>
            <td colspan="3"><strong>FINAL AVERAGE GRADE: </strong> {{ $asr->grade?->name ?? '—' }}</td>
            <td colspan="3"><strong>CLASS AVERAGE GRADE: </strong> {{ MK::getGradeNameByScore($asr->class_average) ?? '—' }}</td>
        </tr>
    </tbody>
</table>

<p><strong style="text-decoration: underline;">KEY</strong>: CW => Course Work, HW => Home Work, TT => Topic Test</p>
