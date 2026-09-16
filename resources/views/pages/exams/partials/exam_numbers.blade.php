@if ($students->isEmpty())
    <div class="alert alert-warning">No students found for the selected class/section.</div>
@else
    <div class="table-responsive">
        <table class="table-sm table-hover table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Section</th>
                    <th>Exam Number</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($students as $student)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ $student->user->name }}
                            <small class="text-muted d-block">{{ $student->admission_number }}</small>
                        </td>
                        <td>{{ $student->section->name ?? '—' }}</td>
                        <td>
                            <input
                                type="text"
                                class="form-control form-control-sm exam-number-input"
                                data-student-id="{{ $student->user_id }}"
                                value="{{ $numbers[$student->user_id] ?? '' }}"
                                placeholder="Not generated yet"
                            />
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

@endif
