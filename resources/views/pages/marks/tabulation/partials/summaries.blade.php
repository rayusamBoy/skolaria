<div class="row" style="display: flex; gap: 1rem">
    {{--Metric Summary--}}
    {!! Mk::renderGenderWiseSummary($exam_id, $my_class->id, $my_class->class_type_id, $section_id, 'both') !!}
    {{--Grades Criteria--}}
    <div id="grades-criteria" data-toggle-target="#grades-criteria" class="toggleable mw-fit col-3 w-60">
        <strong style="font-size: 15px">Grade Criteria</strong>
        {!! Mk::renderGradeTable($class_type_id) !!}
    </div>
    {{--Division Criteria--}}
    <div id="divisions-criteria" data-toggle-target="#divisions-criteria" class="toggleable mw-fit col-3 w-60">
        <strong style="font-size: 15px">Division Criteria</strong>
        {!! Mk::renderDivisionTable($class_type_id) !!}
    </div>
    {{--Overall GPA--}}
    <div id="overall-gpa" data-toggle-target="#overall-gpa" class="toggleable col-3 text-center">
        <strong style="font-size: 15px">Overall GPA</strong>
        <table class="table-styled float-head text-center">
            <thead>
                <tr>
                    <td>{{ Mk::getGPA($exam_id, $my_class->id, $my_class->class_type_id, $section_id) }}</td>
                </tr>
            </thead>
        </table>
    </div>
</div>
