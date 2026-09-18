<table class="table-styled tabulation-table float-head" border="1" style="width: 100%; border-collapse: collapse">
    <thead style="background: #f5f5f5">
        <tr>
            <th class="text-center">#</th>
            <th class="text-center">INDEX NUMBER</th>
            <th class="text-center">Gender</th>
            @foreach ($subjects as $sub)
                <th class="text-center" title="{{ $sub->name }}">{{ $sub->slug ?? $sub->name }}</th>
                <th class="text-center subject-mark">Grd</th>
            @endforeach
            <th id="header-total" class="col-total toggleable text-center" data-toggle-target=".col-total">Total</th>
            <th id="header-avg" class="col-avg toggleable text-center" title="Average" data-toggle-target=".col-avg">
                Avg Grade
            </th>
            <th id="header-grade" class="text-center col-grade toggleable" title="Grade" data-toggle-target=".col-grade">Grd</th>
            <th
                id="header-points"
                class="col-points toggleable text-center"
                title="Points"
                data-toggle-target=".col-points"
            >
                Pts
            </th>
            <th
                id="header-division"
                class="col-division toggleable text-center"
                title="Division"
                data-toggle-target=".col-division"
            >
                Div
            </th>
            <th
                id="header-gpa"
                class="col-gpa toggleable text-center"
                title="Grade Point Average"
                data-toggle-target=".col-gpa"
            >
                GPA
            </th>
            <th
                id="header-position"
                class="col-position toggleable text-center"
                title="Position"
                data-toggle-target=".col-position"
            >
                Pos
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($students_data as $row)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $row['exam_number'] ?? $row['user']->username }}</td>
                <td class="text-center">{{ substr($row['gender'], 0, 1) }}</td>

                @foreach ($subjects as $sub)
                    @php $sub_mark = $row['sub_marks'][$sub->id]; @endphp
                    <td class="text-center subject-mark" data-toggle-target='.subject-mark' data-label="Subject marks">{{ $sub_mark['mark'] ?? '—' }}</td>
                    <td class="text-center text-green">{{ $sub_mark['grade'] ?? '—' }}</td>
                @endforeach
                <td class="text-danger col-total text-center">{{ $row['total'] ?? '—' }}</td>
                <td class="text-secondary col-average text-center">{{ $row['grade'] ?? '—' }}</td>
                <td class="text-center text-info col-grade">{{ $row['grade'] ?? '—' }}</td>
                <td class="text-pink col-points text-center">{{ $row['points'] ?? '—' }}</td>
                <td class="text-orange col-division text-center">{{ $row['division'] ?? '—' }}</td>
                <td class="text-violet col-gpa text-center">{{ $row['gpa'] ?? '—' }}</td>
                <td class="text-primary col-position text-center">{{ $row['pos'] ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
