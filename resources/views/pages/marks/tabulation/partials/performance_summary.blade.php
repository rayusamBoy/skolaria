<div id="performance-summary" class="toggleable" data-toggle-target="#performance-summary" style="margin-top: 25px;">
    <strong>Subjects Performance Summary</strong>
    <table class="table-styled float-head" border="1" style="width: 100%;">
        <thead>
            <tr>
                <th class="font-weight-bold text-center">SLUG</th>
                <th class="font-weight-bold text-center">NAME</th>
                <th class="font-weight-bold text-center">REG</th>
                <th class="font-weight-bold text-center">SAT</th>
                <th class="font-weight-bold text-center">ABS</th>
                <th class="font-weight-bold text-center">AVG</th>
                <th class="font-weight-bold text-center">AVG RANK</th>
                <th class="font-weight-bold text-center">GRADE</th>
                <th class="font-weight-bold text-center">GRADES COUNT</th>
                @if($ex->exam_student_position_by_value === array_keys(Mk::getStudentExamPositionByValues())[3])
                <th class="font-weight-bold text-center">GPA</th>
                @endif
            </tr>
        </thead>
        <tbody>
            {!! Mk::renderSubjectPerformanceSummary($section_id, $ex->id, $my_class, $ex->exam_student_position_by_value) !!}
        </tbody>
    </table>
    <p style="margin-top: 10px;">
        <strong>KEY:</strong> SAT = Attended, ABS = Absent, REG = Registered, AVG = Average
    </p>
</div>
