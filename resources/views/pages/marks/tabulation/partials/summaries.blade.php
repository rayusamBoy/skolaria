<div class="row" style="display: flex; gap: 1rem;">
    {{--Division Summary--}}
    <div id="division-summary" data-toggle-target="#division-summary" class="toggleable col-3 mw-fit w-60">
        <strong style="font-size: 15px;">Division Summary</strong>
        {!! Mk::renderDivisionsSummary($exam_id, $my_class->id, $my_class->class_type_id, $section_id) !!}
    </div>
    {{--Grades Criteria--}}
    <div id="grades-criteria" data-toggle-target="#grades-criteria" class="toggleable col-3 mw-fit w-60">
        <strong style="font-size: 15px;">Grade Criteria</strong>
        {!! Mk::renderGrades($class_type_id) !!}
    </div>
    {{--Division Criteria--}}
    <div id="divisions-criteria" data-toggle-target="#divisions-criteria" class="toggleable col-3 mw-fit w-60">
        <strong style="font-size: 15px;">Division Criteria</strong>
        {!! Mk::renderDivisions($class_type_id) !!}
    </div>
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
</div>
