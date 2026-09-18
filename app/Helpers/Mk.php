<?php

namespace App\Helpers;

use App\Models\Division;
use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\Subject;
use App\Models\SubjectRecord;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mk extends Qs
{
    /** @var int|null Cached exam lock status to avoid repeated calls to settings. */
    private static ?int $lockExamCache = null;

    /**
     * Check if the exam subsystem is locked globally.
     */
    public static function examIsLocked(): bool
    {
        if (self::$lockExamCache === null) {
            self::$lockExamCache = (int) self::getSetting('lock_exam');
        }

        return self::$lockExamCache === 1;
    }

    /**
     * Return a list of predefined remarks for grades/divisions.
     */
    public static function getRemarks(): array
    {
        return [
            'Average',
            'Credit',
            'Distinction',
            'Excellent',
            'Satisfactory',
            'Subsidiary',
            'Fail',
            'Fair',
            'Good',
            'Pass',
            'Poor',
            'Very Good',
            'Very Poor',
        ];
    }

    /**
     * Calculate the average of a sum divided by a count.
     */
    public static function getAverage(float|int $sum, int $number, int $precision = 1): float
    {
        return round($sum / $number, $precision);
    }

    /**
     * Get distinct subjects that have marks for the given conditions.
     */
    public static function getDistinctMarkSubjects(array $where): \Illuminate\Support\Collection
    {
        return Mark::where($where)->with('subject')->get()->pluck('subject')->unique();
    }

    /**
     * Get grades for a specific class type.
     */
    public static function getGrades(int $class_type_id): Collection
    {
        return Grade::where('class_type_id', $class_type_id)->orderBy('name')->get();
    }

    /**
     * Render a performance summary table for subjects, showing grade distribution, averages, ranks, and GPA.
     */
    public static function renderSubjectPerformanceSummary(int|string $section_id, int $exam_id, Collection|Model $my_class, string $exam_student_position_by_value): string
    {
        $collection = '';
        $grades = self::getGrades($my_class->class_type_id);
        $base = [
            'my_class_id' => $my_class->id,
            'exam_id' => $exam_id,
        ];
        if ($section_id !== 'all') {
            $base['section_id'] = $section_id;
        }

        $marks_subjects = self::getDistinctMarkSubjects($base);

        // Preload all marks for these subjects to avoid repeated queries
        $all_marks = Mark::where($base)
            ->whereIn('subject_id', $marks_subjects->pluck('id'))
            ->get()
            ->groupBy('subject_id');

        // Preload student IDs for each subject (for registered count)
        $subject_student_ids = [];
        foreach ($marks_subjects as $subject) {
            $subject_marks = $all_marks[$subject->id] ?? collect();
            $sids = $subject_marks->pluck('student_id')->unique()->values();
            if ($sids->isEmpty()) {
                $record = SubjectRecord::where('subject_id', $subject->id)->first();
                if ($record?->students_ids) {
                    $sids = collect($record->students_ids);
                }
            }
            $subject_student_ids[$subject->id] = $sids;
        }

        // Ranking data
        $all_avg_sum = [];
        foreach ($marks_subjects as $subject) {
            $sids = $subject_student_ids[$subject->id];
            $marks = $all_marks[$subject->id] ?? collect();
            $filtered = $marks->whereIn('student_id', $sids);
            $all_avg_sum[] = (int) $filtered->avg('exam_mark');
        }
        rsort($all_avg_sum);
        $all_avg_sum_flipped = array_flip($all_avg_sum);

        foreach ($marks_subjects as $subject) {
            $sids = $subject_student_ids[$subject->id];
            $marks = $all_marks[$subject->id] ?? collect();
            $filtered = $marks->whereIn('student_id', $sids);

            $reg = $sids->count();
            $sat = $filtered->whereNotNull('exam_mark')->count();
            $total_sum = $filtered->sum('exam_mark');
            $avg = $sat == 0 ? 0 : round($total_sum / $sat, 1);
            $avg_sum = (int) $filtered->avg('exam_mark');

            // Grade distribution
            $grade_counts = $filtered
                ->groupBy('grade_id')
                ->map(fn ($students) => $students->count())
                ->toArray();

            $collection .= "<tr><td class='text-center'>{$subject->slug}</td><td class='text-center'>{$subject->name}</td><td class='text-center'>$reg</td><td class='text-center'>$sat</td><td class='text-center'>".$reg - $sat.'</td>'
                .'<td class="text-center">'.$avg.'</td>'
                .'<td class="text-center">'.self::getOrdinalSuffix(($all_avg_sum_flipped[$avg_sum] ?? 0) + 1).'</td>'
                .'<td class="text-green text-center">'.self::getGradeNameByScore($avg, true).'</td>'
                .'<td><table style="border-collapse: collapse;"><tr>';

            $total_grade_points = 0;
            $total_count = 0;
            foreach ($grades as $grd) {
                $count = $grade_counts[$grd->id] ?? 0;
                $collection .= "<td class='w-1pcnt unstyled text-center'><strong>{$grd->name}</strong>: "
                    .\sprintf('%02d', $count).'</td>';
                $total_grade_points += $grd->point * $count;
                $total_count += $count;
            }

            $gpa = $total_count > 0 ? round($total_grade_points / $total_count, 2) : 0;
            $collection .= match ($exam_student_position_by_value) {
                array_keys(self::getStudentExamPositionByValues())[3] => "</tr></table></td><td class='text-center'>$gpa</td></tr>",
                default => '</tr></table></td></tr>',
            };
        }

        return $collection;
    }

    /**
     * Calculate overall GPA for an exam, optional class and section, using credit-weighted grade points.
     */
    public static function getGPA(
        int $exam_id,
        ?int $my_class_id,
        int $class_type_id,
        mixed $section_id,
        int $precision = 2
    ): float {
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

        $weighted_sum = $query->sum(DB::raw('subjects.credits * grades.point'));
        $total_credits = $query->sum('subjects.credits');

        return $total_credits == 0 ? 0.00 : round($weighted_sum / $total_credits, $precision);
    }

    /**
     * Get distinct exam records for a section (or all) with student data.
     *
     * @param  mixed  $section_id
     */
    public static function getExamRecords($section_id, int $exam_id, int $class_id): Collection
    {
        $conditions = [
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
        ];
        if ($section_id !== 'all') {
            $conditions['section_id'] = $section_id;
        }

        return ExamRecord::where($conditions)
            ->with('student')
            ->distinct()
            ->get();
    }

    /**
     * Render a gender‑wise summary table for grades and divisions.
     *
     * @param  mixed  $section_id  Section ID or 'all'
     * @param  string  $type  'grades', 'divisions', or 'both' (default)
     */
    public static function renderGenderWiseSummary(int $exam_id, int $class_id, int $class_type_id, $section_id, string $type = 'both'): string
    {
        $exam_records = self::getExamRecords($section_id, $exam_id, $class_id)
            ->whereNotNull('student')
            ->where('total', '<>', 0); // attended only

        // Grades list (with class‑type fallback)
        $grades = Grade::where('class_type_id', $class_type_id)->orderBy('name')->get();
        if ($grades->isEmpty()) {
            $grades = Grade::whereNull('class_type_id')->orderBy('name')->get();
        }

        // Divisions list (with class‑type fallback)
        $divisions = Division::where('class_type_id', $class_type_id)->orderBy('name')->get();
        if ($divisions->isEmpty()) {
            $divisions = Division::whereNull('class_type_id')->orderBy('name')->get();
        }

        $html = '';

        // ------- Grades section -------
        if ($type === 'grades' || $type === 'both') {
            $gradeNames = '';
            $maleCounts = '';
            $femaleCounts = '';
            $totalCounts = '';

            foreach ($grades as $grade) {
                $gradeNames .= "<th class='text-center'>{$grade->name}</th>";
                $mc = $exam_records->where('student.gender', User::GENDER_MALE)
                    ->where('grade_id', $grade->id)->count();
                $fc = $exam_records->where('student.gender', User::GENDER_FEMALE)
                    ->where('grade_id', $grade->id)->count();
                $maleCounts .= "<td class='text-center'>$mc</td>";
                $femaleCounts .= "<td class='text-center'>$fc</td>";
                $totalCounts .= '<td class="text-center">'.($mc + $fc).'</td>';
            }

            $html .=
            "
            <div id='averageGrade-summary' data-toggle-target='#averageGrade-summary' class='toggleable mw-fit col-3 w-60'>
                <strong style='font-size: 15px'>Average Grade Summary</strong>
                <table class='table-styled styled float-head'>
                    <thead><tr><th class='text-center'>Average Grade</th>{$gradeNames}</tr></thead>
                    <tbody>
                        <tr><td class='text-center'>Male</td>{$maleCounts}</tr>
                        <tr><td class='text-center'>Female</td>{$femaleCounts}</tr>
                        <tr><td class='text-center'>Total</td>{$totalCounts}</tr>
                    </tbody>
                </table>
            </div>
            ";
        }

        // ------- Divisions section -------
        if ($type === 'divisions' || $type === 'both') {
            $divNames = '';
            $maleCounts = '';
            $femaleCounts = '';
            $totalCounts = '';

            foreach ($divisions as $div) {
                $divNames .= "<th class='text-center'>{$div->name}</th>";
                $mc = $exam_records->where('student.gender', User::GENDER_MALE)
                    ->where('division_id', $div->id)->count();
                $fc = $exam_records->where('student.gender', User::GENDER_FEMALE)
                    ->where('division_id', $div->id)->count();
                $maleCounts .= "<td class='text-center'>$mc</td>";
                $femaleCounts .= "<td class='text-center'>$fc</td>";
                $totalCounts .= '<td class="text-center">'.($mc + $fc).'</td>';
            }

            $html .= "
            <div id='division-summary' data-toggle-target='#division-summary' class='toggleable mw-fit col-3 w-60'>
                <strong style='font-size: 15px'>Division Summary</strong>
                <table class='table-styled styled float-head' style='margin-top:1em;'>
                    <thead><tr><th class='text-center'>Division</th>{$divNames}</tr></thead>
                    <tbody>
                        <tr><td class='text-center'>Male</td>{$maleCounts}</tr>
                        <tr><td class='text-center'>Female</td>{$femaleCounts}</tr>
                        <tr><td class='text-center'>Total</td>{$totalCounts}</tr>
                    </tbody>
                </table>
            </div>
            ";
        }

        return $html;
    }

    /**
     * Return a number with an ordinal suffix (English).
     */
    public static function getOrdinalSuffix(int $number): ?string
    {
        if ($number < 1) {
            return null;
        }
        $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if ($number % 100 >= 11 && $number % 100 <= 13) {
            return "$number<sup>th</sup>";
        }

        return "$number<sup>".$ends[$number % 10].'</sup>';
    }

    /**
     * Count marks with grade names starting with A or B (distinctions).
     */
    public static function countDistinctions(Collection $marks): int
    {
        $ids = Grade::where('name', 'LIKE', 'A%')->orWhere('name', 'LIKE', 'B%')->pluck('id')->toArray();

        return self::markGradeFilter($marks, $ids);
    }

    /**
     * Count marks with grade names starting with D or E (passes).
     */
    public static function countPasses(Collection $marks): int
    {
        $ids = Grade::where('name', 'LIKE', 'D%')->orWhere('name', 'LIKE', 'E%')->pluck('id')->toArray();

        return self::markGradeFilter($marks, $ids);
    }

    /**
     * Count marks with grade names starting with C (credits).
     */
    public static function countCredits(Collection $marks): int
    {
        $ids = Grade::where('name', 'LIKE', 'C%')->pluck('id')->toArray();

        return self::markGradeFilter($marks, $ids);
    }

    /**
     * Count marks with grade names starting with F (failures).
     */
    public static function countFailures(Collection $marks): int
    {
        $ids = Grade::where('name', 'LIKE', 'F%')->pluck('id')->toArray();

        return self::markGradeFilter($marks, $ids);
    }

    /**
     * Filter marks by a set of grade IDs and return count.
     */
    protected static function markGradeFilter(Collection $marks, array $gradeIds): int
    {
        return $marks->filter(fn ($mk) => \in_array($mk->grade_id, $gradeIds))->count();
    }

    /**
     * Count subjects a student actually took (total tca+exam_mark > 0).
     */
    public static function countSubjectsOffered(Collection $marks): int
    {
        return $marks->filter(fn ($mk) => $mk->tca + $mk->exam_mark > 0)->count();
    }

    /**
     * Get grade list for a class type, falling back to global grades if empty.
     */
    public static function getGradeList(int $class_type_id): Collection
    {
        $grades = Grade::where('class_type_id', $class_type_id)->orderBy('name')->get();

        return $grades->isNotEmpty() ? $grades : Grade::whereNull('class_type_id')->orderBy('name')->get();
    }

    /**
     * Get division list for a class type, falling back to global divisions if empty.
     */
    public static function getDivisionList(int $class_type_id): Collection
    {
        $divisions = Division::where('class_type_id', $class_type_id)->orderBy('name')->get();

        return $divisions->isNotEmpty() ? $divisions : Division::whereNull('class_type_id')->orderBy('name')->get();
    }

    /**
     * Count exam records for a section in an exam/class.
     */
    public static function getSectionCount(int $exam_id, int $class_id, int $section_id): int
    {
        return ExamRecord::where([
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
            'section_id' => $section_id,
        ])->count();
    }

    /**
     * Count exam records for a whole class.
     */
    public static function getClassCount(int $exam_id, int $class_id): int
    {
        return ExamRecord::where([
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
        ])->count();
    }

    /**
     * Delete marks and exam records from a previous class/section when a student changes.
     */
    public static function deleteOldClassRecords(int $st_id, int $class_id): bool
    {
        $conditions = ['student_id' => $st_id, 'year' => self::getCurrentSession()];

        $marks = Mark::where('my_class_id', '<>', $class_id)->where($conditions);
        if ($marks->exists()) {
            $marks->delete();
            ExamRecord::where('my_class_id', '<>', $class_id)->where($conditions)->delete();
        }

        return true;
    }

    /**
     * Get divisions for a specific class type.
     */
    public static function getDivisionsByClassType(int $class_type_id): Collection
    {
        return Division::where('class_type_id', $class_type_id)->get();
    }

    /**
     * Determine division based on points and class type, fallback to global divisions.
     */
    public static function getDivisionByPoints(float|int $points, int $class_type_id): ?Division
    {
        $divisions = self::getDivisionsByClassType($class_type_id);
        if ($divisions->isNotEmpty()) {
            $div = $divisions->where('points_from', '<=', $points)
                ->where('points_to', '>=', $points)->first();

            return $div ?: self::getGlobalDivisionByPoints($points);
        }

        return self::getGlobalDivisionByPoints($points);
    }

    /**
     * Fallback: division lookup from global (class-type-independent) divisions.
     */
    public static function getGlobalDivisionByPoints(float|int $points): ?Division
    {
        return Division::whereNull('class_type_id')
            ->where('points_from', '<=', $points)
            ->where('points_to', '>=', $points)
            ->first();
    }

    /**
     * Get grade name for an average, optionally with remarks.
     */
    public static function getGradeNameByScore(float|int $marks_avg, bool $with_remark = false): ?string
    {
        $marks_avg = (int) round($marks_avg);
        $grade = Grade::where('mark_from', '<=', $marks_avg)->where('mark_to', '>=', $marks_avg);

        if ($with_remark) {
            return $grade->value('name').' - '.$grade->value('remarks');
        }

        return $grade->value('name');
    }

    /**
     * Generate HTML table showing grade thresholds for a class type.
     */
    public static function renderGradeTable(int $class_type_id): string
    {
        $grades = Grade::where('class_type_id', $class_type_id)->orderBy('name')->get();
        if ($grades->isEmpty()) {
            $grades = Grade::whereNull('class_type_id')->orderBy('name')->get();
        }

        $grade_name = $marks_from = $marks_to = $points = '';
        foreach ($grades as $grade) {
            $grade_name .= "<th class='text-center'>{$grade->name}</th>";
            $marks_from .= "<td class='text-center'>{$grade->mark_from}</td>";
            $marks_to .= "<td class='text-center'>{$grade->mark_to}</td>";
            $points .= "<td class='text-center'>{$grade->point}</td>";
        }

        return "<table class='table-styled float-head'><thead><tr><th class='text-center'>Grade</th>$grade_name</tr></thead><tbody><tr><td class='text-center'>Mark From</td>$marks_from</tr><tr><td class='text-center'>Mark To</td>$marks_to</tr><tr><td class='text-center'>Point</td>$points</tr></tbody></table>";
    }

    /**
     * Generate HTML table showing division thresholds for a class type.
     */
    public static function renderDivisionTable(int $class_type_id): string
    {
        $divisions = Division::where('class_type_id', $class_type_id)->orderBy('name')->get();
        if ($divisions->isEmpty()) {
            $divisions = Division::whereNull('class_type_id')->orderBy('name')->get();
        }

        $name = $points_from = $points_to = '';
        foreach ($divisions as $division) {
            $name .= "<th class='text-center'>{$division->name}</th>";
            $points_from .= "<td class='text-center'>{$division->points_from}</td>";
            $points_to .= "<td class='text-center'>{$division->points_to}</td>";
        }

        return "<table class='table-styled float-head'><thead><tr><th class='text-center'>Division</th>$name</tr></thead><tbody><tr><td class='text-center'>Points From</td>$points_from</tr><tr><td class='text-center'>Points To</td>$points_to</tr></tbody></table>";
    }

    /**
     * Get subjects for a class, respecting teacher role if applicable.
     */
    public static function getSubjects(int $class_id): Collection
    {
        if (auth()->user()->hasRole(config('roles.default_roles.tenant.teacher'))) {
            return SubjectRecord::where('teacher_id', auth()->id())
                ->rightJoin(Subject::getTable(), function ($join) {
                    $join->on(SubjectRecord::getTable().'.subject_id', '=', Subject::getTable().'.id');
                })
                ->where(Subject::getTable().'.my_class_id', $class_id)
                ->distinct()
                ->get();
        }

        return Subject::where('my_class_id', $class_id)->distinct()->get();
    }

    /**
     * Options for student exam position calculation.
     * These must match the column names in the exam_records table.
     */
    public static function getStudentExamPositionByValues(): array
    {
        return [
            'total' => 'Total Marks',
            'average' => 'Average Mark',
            'points' => 'Division Points',
            'gpa' => 'GPA',
        ];
    }

    /**
     * Options for continuous assessment position calculation.
     */
    public static function getStudentCAPositionByValues(): array
    {
        return [
            'total' => 'Total Marks',
            'average' => 'Average Mark',
        ];
    }
}
