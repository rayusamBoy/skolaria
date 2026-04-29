<?php

namespace App\Helpers;

use App\Models\AssessmentRecord;
use App\Models\Division;
use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectRecord;
use App\Models\TimeTable;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mk extends Qs
{
    public static function examIsLocked()
    {
        return (int) self::getSetting('lock_exam') === 1;
    }

    public static function getRemarks()
    {
        return ['Average', 'Credit', 'Distinction', 'Excellent', 'Satisfactory', 'Subsidiary', 'Fail', 'Fair', 'Good', 'Pass', 'Poor', 'Very Good', 'Very Poor'];
    }

    public static function getAverage($sum, $number, $precision = 1)
    {
        return round($sum / $number, $precision);
    }

    public static function getPercentage($item, $sum, $precicion)
    {
        return round(($item / $sum) * 100, $precicion) . '%';
    }

    public static function getAssessmentRec($student_id, $sub_id, $exam_id)
    {
        return AssessmentRecord::where(["student_id" => $student_id, "subject_id" => $sub_id, "exam_id" => $exam_id])->get()->first();
    }

    // Base method for marks - must be used for all the functions rendering content in marks tabulation view - for consistent, uniform rendering
    public static function getDistinctMrksSbjcts(array $where)
    {
        return Mark::where($where)->with('subject')->get()->pluck('subject')->unique();
    }

    public static function getGrades($class_type_id)
    {
        return Grade::where('class_type_id', $class_type_id)->orderBy('name')->get();
    }

    /**
     * Renders the subject performance summary for a given section, exam, and class.
     */
    public static function renderSubsPerformanceSummary(int|string $section_id, int $exam_id, Collection|Model $my_class): string
    {
        $collection = '';
        $all_avg_sum = [];
        $grades = self::getGrades($my_class->class_type_id);

        // Base query
        $base_conditions = [
            'my_class_id' => $my_class->id,
            'exam_id' => $exam_id,
        ];
        if ($section_id !== 'all') {
            $base_conditions['section_id'] = $section_id;
        }

        $marks_subjects = self::getDistinctMrksSbjcts($base_conditions);

        // Compute average exam sums for ranking
        foreach ($marks_subjects as $subject) {
            $p = Mark::where([...$base_conditions, 'subject_id' => $subject->id]);
            $sids = $p->pluck('student_id');
            $reg = $sids->isNotEmpty()
                ? $sids->count()
                : (($sids = SubjectRecord::where('subject_id', $subject->id)->get()->value('students_ids')) !== null
                    ?: \count($sids ?? []));

            $avg_sum = (int) Mark::where([...$base_conditions, 'subject_id' => $subject->id])
                ->whereIn('student_id', $sids)
                ->avg('exm');

            $all_avg_sum[] = $avg_sum;
        }

        rsort($all_avg_sum);
        $all_avg_sum_flipped = array_flip($all_avg_sum);

        // Render each subject row
        foreach ($marks_subjects as $subject) {
            $p = Mark::where([...$base_conditions, 'subject_id' => $subject->id]);
            $sids = $p->pluck('student_id');
            $reg = $sids->isNotEmpty()
                ? $sids->count()
                : (($sids = SubjectRecord::where('subject_id', $subject->id)->get()->value('students_ids')) !== null
                    ?: \count($sids ?? []));

            // Sat count and average score
            $sQuery = Mark::where([...$base_conditions, 'subject_id' => $subject->id])
                ->whereIn('student_id', $sids);

            $sat = (clone $sQuery)->whereNotNull('exm')->count();
            $avg_sum = (int) $sQuery->avg('exm');
            $avg = $sat == 0 ? $sat : round($sQuery->sum('exm') / $sat, 1);

            // Grade distribution
            $grade_counts = Mark::where([...$base_conditions, 'subject_id' => $subject->id])
                ->whereIn('student_id', $sids)
                ->select('grade_id', DB::raw('COUNT(*) as count'))
                ->groupBy('grade_id')
                ->pluck('count', 'grade_id')
                ->toArray();

            // Start table row
            $collection .=
                '<tr>
                    <td class="text-center">' . $subject->slug . '</td>
                    <td class="text-center">' . $subject->name . '</td>
                    <td class="text-center">' . $reg . '</td>
                    <td class="text-center">' . $sat . '</td>
                    <td class="text-center">' . $reg - $sat . '</td>
                    <td class="text-center">' . $avg . '</td>
                    <td class="text-center">' . Mk::getSuffix($all_avg_sum_flipped[$avg_sum] + 1) . '</td>
                    <td class="text-green text-center">' . self::getGrade($avg, true) . '</td>
                    <td><table style="border-collapse: collapse;">
                <tr>';

            $total_grade_points = 0;
            $total_count = 0;

            foreach ($grades as $grd) {
                $count = $grade_counts[$grd->id] ?? 0;
                $collection .= "<td class='w-1pcnt unstyled text-center'><strong>{$grd->name}</strong>: " . \sprintf("%02d", $count) . '</td>';

                // GPA = Σ (point × credits) / Σ (credits)
                // In the context of one subject, the credits are constant across all students taking it. Therefore:
                // Σ (point × credits) = credits × Σ (point × count)
                // GPA = (credits × Σ (point × count)) / (credits × total_count) = Σ (point × count) / total_count
                $total_grade_points += $grd->point * $count;
                $total_count += $count;
            }

            // GPA = average grade point for the subject (credits cancel out)
            $gpa = $total_count > 0 ? round($total_grade_points / $total_count, 2) : 0;

            $collection .= "</tr></table></td><td class='text-center'>$gpa</td></tr>";
        }

        return $collection;
    }
    /** GRADES SUMMARY ENDS **/

    /**
     * Calculate GPA for a given exam, class, class type, and section.
     */
    public static function getGPA(int $exam_id, int|null $my_class_id, int $class_type_id, mixed $section_id, int $precision = 2)
    {
        $query = Mark::where('marks.exam_id', $exam_id)
            ->join('subjects', 'marks.subject_id', '=', 'subjects.id')
            ->join('grades', 'marks.grade_id', '=', 'grades.id')
            ->where('grades.class_type_id', $class_type_id);

        if ($my_class_id) {
            $query->where('marks.my_class_id', $my_class_id);
            if ($section_id && $section_id !== 'all') {
                $query->where('marks.section_id', $section_id);
            }
        }

        // Calculate weighted sum (credits * point) and total credits
        $weighted_sum = $query->sum(DB::raw('subjects.credits * grades.point'));
        $total_credits = $query->sum('subjects.credits');

        // Avoid division by zero
        if ($total_credits == 0) {
            return 0.00;
        }

        return round($weighted_sum / $total_credits, $precision);
    }

    /** DIVISION SUMMARY */
    public static function getExamRecords($section_id, $exam_id, $class_id)
    {
        $d = ($section_id == 'all') ? ['exam_id' => $exam_id, 'my_class_id' => $class_id] : ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id];
        return ExamRecord::where($d)->with('student')->distinct()->get();
    }

    public static function renderDivisionsSummary($exam_id, $class_id, $class_type_id, $section_id)
    {
        $divison_name = $male_count = $female_count = $count_per_div = $attended = $absent = '';

        $exam_records = self::getExamRecords($section_id, $exam_id, $class_id)->whereNotNull('student'); // Exclude soft deleted users (students)

        // Take only exam records where among others total marks not equal to zero.
        // If total marks equals zero implies the student did not perform any exam.
        // Literary is not possible for student to get zero summation of all exams.
        $exr_attended = $exam_records->where('total', '<>', 0);
        $exr_absent = $exam_records->where('total', 0); // Take only absent ie., where total is zero

        $class_type_divisions = Division::where('class_type_id', $class_type_id);
        $divisions = $class_type_divisions->get();
        $divisions = $divisions ?: Division::whereNull('class_type_id')->get();

        foreach ($divisions as $div) {
            $divison_name .= "<th class='text-center'>{$div->name}</th>";
            $male_count .= '<td class="text-center">' . ($mc = $exr_attended->where('student.gender', User::GENDER_MALE)->where('division_id', $div->id)->whereNotNull('division_id')->count()) . '</td>';
            $female_count .= '<td class="text-center">' . ($fc = $exr_attended->where('student.gender', User::GENDER_FEMALE)->where('division_id', $div->id)->whereNotNull('division_id')->count()) . '</td>';
            $count_per_div .= '<td class="text-center">' . $mc + $fc . "</td>";
        }

        $colspan = ($count = $divisions->count() + 1) / 2; // 1 - for Division column header, and 2 for two required columns - attended and absent

        // Sometimes the count may be odd. Thus, take  the count minus the arleady used colspan (for absent) so that to span to all the remaining columns.
        $attended .= '</tr><tr><td class="text-center" colspan="' . round($count - $colspan) . '">SAT: ' . $exr_attended->whereNotNull('division_id')->count() . '</td>';
        $absent .= '<td class="text-center" colspan="' . round($colspan) . '">ABS: ' . $exr_absent->count() . '</td>';

        $a = '<table class="table-styled styled float-head"><thead><tr><th class="text-center">Division</th>';
        $b = '</tr></thead><tbody><tr><td class="text-center">Male</td>';
        $c = '</tr><tr><td class="text-center">Female</td>';
        $d = '</tr><tr><td class="text-center">Total</td>';
        $g = '</tr></tbody></table>';

        $collection = "$a$divison_name$b$male_count$c$female_count$d$count_per_div$attended$absent$g";
        return $collection;
    }

    /** DIVISION SUMMARY ENDS */

    /**
     * Adds an ordinal suffix to a given number.
     *
     * @param int $number The number to add the ordinal suffix to.
     * @return string|null The number with the ordinal suffix, or null if the input number is less than 1.
     */
    public static function getSuffix(int $number)
    {
        if ($number < 1)
            return null;

        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if ($number % 100 >= 11 && $number % 100 <= 13)
            return "$number<sup>th</sup>";
        else
            return "$number<sup>" . $ends[$number % 10] . '</sup>';
    }

    /*Get Subject Total Per Term*/
    public static function getSubTotalTerm($st_id, $sub_id, $term, $class_id, $year)
    {
        $d = ['student_id' => $st_id, 'subject_id' => $sub_id, 'my_class_id' => $class_id, 'year' => $year];
        $tex = "tex$term";
        $sub_total = Mark::where($d)->select($tex)->where($tex, '>', 0)->first();

        return $sub_total ? $sub_total->{$tex} : '—';
    }

    public static function countDistinctions(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'A%')->orWhere('name', 'LIKE', 'B%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countPasses(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'D%')->orWhere('name', 'LIKE', 'E%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countCredits(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'C%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countFailures(Collection $marks)
    {
        $gradeIDS = Grade::where('name', 'LIKE', 'F%')->get()->pluck('id')->toArray();
        return self::markGradeFilter($marks, $gradeIDS);
    }

    public static function countStudents($exam_id, $class_id, $section_id, $year)
    {
        $d = ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $year];
        return Mark::where($d)->select('student_id')->distinct()->get()->count();
    }

    protected static function markGradeFilter(Collection $marks, $gradeIDS)
    {
        return $marks->filter(fn($mks) => \in_array($mks->grade_id, $gradeIDS))->count();
    }

    public static function countSubjectsOffered(Collection $mark)
    {
        return $mark->filter(fn($mk) => $mk->tca + $mk->exm > 0)->count();
    }

    /* Get Exam Avg Per Term */
    public static function getTermAverage($st_id, $term, $year)
    {
        $exam = self::getExamByTerm($term, $year);
        $d = ['exam_id' => $exam->id, 'student_id' => $st_id, 'year' => $year];

        if ($term < 3) {
            $exr = ExamRecord::where($d);
            $avg = $exr->first()->ave ?: null;
            return $avg > 0 ? round($avg, 1) : $avg;
        }

        $mk = Mark::where($d)->whereNotNull('tex3');
        $avg = $mk->select('tex3')->avg('tex3');
        return round($avg, 1);
    }

    public static function getTermTotal($st_id, $term, $year)
    {
        $exam = self::getExamByTerm($term, $year);
        $d = ['exam_id' => $exam->id, 'student_id' => $st_id, 'year' => $year];

        if ($term < 3)
            return ExamRecord::where($d)->first()->total ?? null;

        $mk = Mark::where($d)->whereNotNull('tex3');
        return $mk->select('tex3')->sum('tex3');
    }

    public static function getExamByTerm($term, $year)
    {
        $d = ['term' => $term, 'year' => $year];
        return Exam::where($d)->first();
    }

    public static function getGradeList($class_type_id)
    {
        $grades = Grade::where(['class_type_id' => $class_type_id])->orderBy('name')->get();

        if ($grades->count() < 1)
            $grades = Grade::whereNull('class_type_id')->orderBy('name')->get();

        return $grades;
    }

    public static function gateDivisionList($class_type_id)
    {
        $divisions = Division::where(['class_type_id' => $class_type_id])->orderBy('name')->get();

        if ($divisions->count() < 1)
            $divisions = Division::whereNull('class_type_id')->orderBy('name')->get();

        return $divisions;
    }

    public static function getSectionCount($exam_id, $class_id, $section_id)
    {
        return ExamRecord::where(['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id])->count();
    }

    public static function getClassCount($exam_id, $class_id)
    {
        return ExamRecord::where(['exam_id' => $exam_id, 'my_class_id' => $class_id])->count();
    }

    /**
     * If Class/Section is Changed in Same Year,
     * Delete Marks/ExamRecord of Previous Class/Section
     *
     * @param int $st_id
     * @param int $class_id
     * @return bool
     * @static
     */
    public static function deleteOldRecord(int $st_id, int $class_id)
    {
        $d = ['student_id' => $st_id, 'year' => self::getCurrentSession()];

        $marks = Mark::where('my_class_id', '<>', $class_id)->where($d);
        if ($marks->get()->isNotEmpty()) {
            $exr = ExamRecord::where('my_class_id', '<>', $class_id)->where($d);
            $marks->delete();
            $exr->delete();
        }
        return true;
    }

    public static function getDvivisionByClassType($id)
    {
        return Division::where(['class_type_id' => $id])->get();
    }

    public static function getDivision($points, $class_type_id)
    {
        $divisions = self::getDvivisionByClassType($class_type_id);

        if ($divisions->isNotEmpty()) {
            $dv = $divisions->where('points_from', '<=', $points)->where('points_to', '>=', $points);
            return $dv->isNotEmpty() ? $dv->first() : self::getDivision2($points);
        }
        return self::getDivision2($points);
    }

    public static function getDivision2($points)
    {
        $divisions = Division::whereNull('class_type_id')->get();
        if ($divisions->isNotEmpty()) {
            return $divisions->where('points_from', '<=', $points)->where('points_to', '>=', $points)->first();
        }
        return null;
    }

    public static function getGrade($marks_avg, $with_remark = false)
    {
        $marks_avg = round((int) $marks_avg);
        $grd = Grade::where('mark_from', '<=', $marks_avg)->where('mark_to', '>=', $marks_avg);

        return $with_remark ? $grd->value('name') . ' - ' . $grd->value('remark') : $grd->value('name');
    }

    public static function renderGrades($class_type_id)
    {
        $grades_name = $marks_from = $marks_to = $points = '';
        $grades = ($class_type_id == 1) ? Grade::where('class_type_id', 1)->orderBy('name')->get() : ($class_type_id == 2 ? Grade::where('class_type_id', 2)->orderBy('name')->get() : Grade::where('class_type_id', 3)->orderBy('name')->get());

        foreach ($grades as $grade) {
            $grades_name .= "<th class='text-center'>{$grade->name}</th>";
            $marks_from .= "<td class='text-center'>{$grade->mark_from}</td>";
            $marks_to .= "<td class='text-center'>{$grade->mark_to}</td>";
            $points .= "<td class='text-center'>{$grade->point}</td>";
        }

        $a = '<table class="table-styled float-head"><thead><tr><th class="text-center">Grade</th>';
        $b = '</tr></thead><tbody><tr><td class="text-center">Mark From</td>';
        $c = '</tr><tr><td class="text-center">Mark To</td>';
        $d = '</tr><tr><td class="text-center">Point</td>';
        $f = '</tr></tbody></table>';

        $collection = "$a$grades_name$b$marks_from$c$marks_to$d$points$f";
        return $collection;
    }

    public static function renderDivisions($class_type_id)
    {
        $name = $points_from = $points_to = '';

        $divisions = ($class_type_id == 1) ? Division::where('class_type_id', 1)->orderBy('name')->get() : ($class_type_id == 2 ? Division::where('class_type_id', 2)->orderBy('name')->get() : Division::where('class_type_id', 3)->orderBy('name')->get());

        foreach ($divisions as $division) {
            $name .= "<th class='text-center'>{$division->name}</th>";
            $points_from .= "<td class='text-center'>{$division->points_from}</td>";
            $points_to .= "<td class='text-center'>{$division->points_to}</td>";
        }

        $a = '<table class="table-styled float-head"><thead><tr><th class="text-center">Division</th>';
        $b = '</tr></thead><tbody><tr><td class="text-center">Points From</td>';
        $c = '</tr><tr><td class="text-center">Points To</td>';
        $d = '</tr></tbody></table>';

        $collection = "$a$name$b$points_from$c$points_to$d";
        return $collection;
    }

    public static function getClassSections($class_id)
    {
        return Section::where(['my_class_id' => $class_id])->orderBy('name', 'asc')->get();
    }

    public static function getSubjectRecords($sub_id)
    {
        return SubjectRecord::where('subject_id', $sub_id)->with(['section', 'teacher'])->get();
    }

    public static function getSubjects($class_id)
    {
        if (auth()->user()->hasRole(config('roles.default_roles.tenant.teacher')))
            return SubjectRecord::where('teacher_id', auth()->id())->rightJoin(Subject::getTable(), function ($rightjoin) {
                $rightjoin->on(SubjectRecord::getTable() . '.subject_id', '=', Subject::getTable() . '.id');
            })->select('*')->where(Subject::getTable() . '.my_class_id', '=', $class_id)->distinct()->get();
        else
            return Subject::where('my_class_id', $class_id)->distinct()->get();
    }

    public static function getStudentExamPositionByValues()
    {
        return [
            'total' => 'Total Marks',
            'ave' => 'Average Mark',
            'points' => 'Division Points',
            'GPA' => 'GPA',
        ];
    }

    public static function getStudentCAPositionByValues()
    {
        return [
            'total' => 'Total Marks',
            'ave' => 'Average Mark',
        ];
    }

    public static function subjectHasChildRows($sub_id)
    {
        $exists = AssessmentRecord::where('subject_id', $sub_id)->exists() or Mark::where('subject_id', $sub_id)->exists() or SubjectRecord::where('subject_id', $sub_id)->exists() or TimeTable::where('subject_id', $sub_id)->exists();
        return $exists;
    }
}
