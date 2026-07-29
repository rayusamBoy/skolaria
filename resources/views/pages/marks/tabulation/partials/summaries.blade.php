<div class="row" style="display: flex; gap: 1rem;">
    {{--Metric Summary--}}
    <div id="division-summary" data-toggle-target="#division-summary" class="toggleable col-3 mw-fit w-60">
        <strong style="font-size: 15px;">Metric Summary</strong>
        {!! Mk::renderGenderWiseSummary($exam_id, $my_class->id, $my_class->class_type_id, $section_id) !!}
    </div>
    @if($ex->exam_student_position_by_value === array_keys(Mk::getStudentExamPositionByValues())[2])
    {{--Grades Criteria--}}
    <div id="grades-criteria" data-toggle-target="#grades-criteria" class="toggleable col-3 mw-fit w-60">
        <strong style="font-size: 15px;">Grade Criteria</strong>
        {!! Mk::renderGradeTable($class_type_id) !!}
    </div>
    {{--Division Criteria--}}
    <div id="divisions-criteria" data-toggle-target="#divisions-criteria" class="toggleable col-3 mw-fit w-60">
        <strong style="font-size: 15px;">Division Criteria</strong>
        {!! Mk::renderDivisionTable($class_type_id) !!}
    </div>
    @endif
    @if($ex->exam_student_position_by_value === array_keys(Mk::getStudentExamPositionByValues())[3])
    {{--Overall GPA--}}
    <div id="overall-gpa" data-toggle-target="#overall-gpa" class="toggleable w-60 col-3 text-center">
        <strong style="font-size: 15px;">Overall GPA</strong>
        <table class="table-styled text-center float-head">
            <thead>
                <tr>
                    <td>{{ Mk::getGPA($exam_id, $my_class->id, $my_class->class_type_id, $section_id) }}</td>
                </tr>
            </thead>
        </table>
    </div>
    @endif
</div>
