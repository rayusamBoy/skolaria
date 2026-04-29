@foreach ($students_summary as $student_id => $row)
<tr>
    <td class="text-center">{{ $loop->iteration }}</td>
    <td class="pl-2">{{ $row['name'] }}</td>
    <td class="gender text-center">{{ substr($row['gender'], 0, 1) }}</td>

    @foreach ($subjects as $sub)
    @php $sub_mark = $row['sub_marks'][$sub->id] ?? ['mark'=>null, 'grade'=>null, 'pos'=>null]; @endphp
    <td class="text-center">{{ $sub_mark['mark'] ?? '—' }}</td>
    <td class="text-center text-info-800">{{ $sub_mark['grade'] ?? '—' }}</td>
    <td class="text-center">{{ $sub_mark['pos'] ?? '—' }}</td>
    @endforeach

    <td class="text-center text-red-600">{{ $row['total'] ?? '—' }}</td>
    <td class="text-center text-blue-800">{{ $row['ave'] ?? '—' }}</td>
    <td class="text-center text-orange-800">{{ $row['grade'] ?? '—' }}</td>
    <td class="text-center text-green-800">{{ $row['pos'] ?? '—' }}</td>
</tr>
@endforeach

<tr>
    <td class="pl-2" colspan="3">SUBJECT TOTAL</td>
    @foreach ($subjects as $sub)
    <td class="text-center" colspan="3">{{ $asr->where('subject_id', $sub->id)->sum($tex) }}</td>
    @endforeach
</tr>
<tr>
    <td class="pl-2" colspan="3">SUBJECT AVERAGE</td>
    @foreach ($subjects as $sub)
    <td class="text-center" colspan="3">{{ round($asr->where('subject_id', $sub->id)->avg($tex), 2) }}</td>
    @endforeach
</tr>
<tr>
    <td class="pl-2" colspan="3">SUBJECT GRADE</td>
    @foreach ($subjects as $sub)
    @php $avg = round($asr->where('subject_id', $sub->id)->avg($tex), 2) @endphp
    <td class="text-center" colspan="3">{{ $avg !== null ? Mk::getGrade($avg) : '' }}</td>
    @endforeach
</tr>
