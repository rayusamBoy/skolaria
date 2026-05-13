<?php

namespace App\Repositories;

use App\Models\Assessment;
use App\Models\AssessmentRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AssessmentRepo
{
    /** @var string */
    protected string $assessments_table;
    /** @var string */
    protected string $assessments_records_table;

    public function __construct()
    {
        $this->assessments_table = 'assessments';
        $this->assessments_records_table = 'assessments_records';
    }

    /*********** Assessments ***************/

    /**
     * Get assessments with their related exam and other relations, filtered by conditions.
     * Excludes assessments whose exam relation is null.
     *
     * @param array $where
     * @return Collection
     */
    public function getAssessmentsWithValidExam(array $where, $with = []): Collection
    {
        return Assessment::with($with)
            ->with('exam')
            ->where($where)
            ->get()
            ->whereNotNull('exam');
    }

    /**
     * Check if there is at least one assessment with a valid exam.
     *
     * @return bool
     */
    public function hasAnyAssessmentWithExam(): bool
    {
        return Assessment::with('exam')
            ->get()
            ->whereNotNull('exam')
            ->isNotEmpty();
    }

    /**
     * Get a single assessment by conditions.
     *
     * @param array $data
     * @return Assessment|null
     */
    public function getAssessmentByConditions(array $data): ?Assessment
    {
        return Assessment::where($data)->first();
    }

    /**
     * Create a new assessment.
     *
     * @param array $data
     * @return Assessment
     */
    public function create(array $data): Assessment
    {
        return Assessment::create($data);
    }

    /**
     * Find an assessment matching the given data or create it.
     *
     * @param array $data
     * @return Assessment
     */
    public function firstOrCreate(array $data): Assessment
    {
        return Assessment::firstOrCreate($data);
    }

    /**
     * Delete assessments matching the given conditions.
     *
     * @param array $data
     * @return int Number of deleted rows
     */
    public function delete(array $data): int
    {
        return Assessment::where($data)->delete();
    }

    /*********** Assessment records ***************/

    /**
     * Create a new assessment record if it doesn't already exist.
     *
     * @param array $data
     * @return AssessmentRecord
     */
    public function createRecord(array $data): AssessmentRecord
    {
        return AssessmentRecord::firstOrCreate($data);
    }

    /**
     * Get assessment records with related exam, user, and optionally other relations.
     * Only records whose exam and user are not null are returned, and duplicates by mark_id are removed.
     *
     * @param array $where
     * @param array $with   Additional relations to eager load
     * @return Collection
     */
    public function getAssessmentRecordsWithRelations(array $where, array $with = ['grade']): Collection
    {
        return AssessmentRecord::where($where)
            ->with($with)
            ->with(['exam', 'user'])
            ->get()
            ->whereNotNull('exam')
            ->whereNotNull('user')
            ->unique('mark_id');
    }

    /**
     * Mass update (upsert) assessment records.
     *
     * @param array $values      Array of record arrays
     * @param array $unique_by   Columns that identify each record
     * @return int               Number of affected rows
     */
    public function massUpdateRecords(array $values, array $unique_by = ['id']): int
    {
        return AssessmentRecord::massUpdate(
            values: $values,
            uniqueBy: $unique_by
        );
    }

    /**
     * Calculate a value proportionally out of a given quantity.
     *
     * @param float $value
     * @param float $quantity
     * @param int   $precision
     * @return float
     */
    public function calculateProportionalValue(float $value, float $quantity, int $precision = 0): float
    {
        return round($value / 100 * $quantity, $precision);
    }

    /**
     * Update a single assessment record.
     *
     * @param array $where
     * @param array $data
     * @return int
     */
    public function updateRecord(array $where, array $data): int
    {
        return AssessmentRecord::where($where)->update($data);
    }

    /**
     * Update assessments table via DB facade.
     *
     * @param array $where
     * @param array $data
     * @return int
     */
    public function updateRecordDBFacades(array $where, array $data): int
    {
        return DB::table($this->assessments_table)->where($where)->update($data);
    }

    /**
     * Get distinct exam years for a student from their assessment records.
     *
     * @param int $student_id
     * @return Collection
     */
    public function getExamYears(int $student_id): Collection
    {
        return AssessmentRecord::where('student_id', $student_id)
            ->select('year')
            ->distinct()
            ->get();
    }

    /**
     * Get a single column value from the first matching assessment record.
     *
     * @param array  $data   Conditions
     * @param string $value  Column name
     * @return mixed
     */
    public function getAssessmentRecordValue(array $data, string $value): mixed
    {
        return AssessmentRecord::where($data)->value($value);
    }

    /**
     * Sum of term marks for a student in a specific exam and class.
     *
     * @param mixed $exam      Exam model
     * @param int   $st_id
     * @param int   $class_id
     * @param string $year
     * @return float
     */
    public function getTotalTermMark($exam, int $st_id, int $class_id, string $year): float
    {
        $d = ['student_id' => $st_id, 'exam_id' => $exam->id, 'my_class_id' => $class_id, 'year' => $year];
        $tex = "tex{$exam->term}";
        return AssessmentRecord::where($d)->select($tex)->sum($tex);
    }

    /**
     * Average of term marks for a student in a specific exam, class, and section.
     *
     * @param mixed $exam
     * @param int   $st_id
     * @param int   $class_id
     * @param int   $sec_id
     * @param string $year
     * @return float
     */
    public function getAverageTermMark($exam, int $st_id, int $class_id, int $sec_id, string $year): float
    {
        $d = [
            'student_id' => $st_id,
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'section_id' => $sec_id,
            'year' => $year
        ];
        $tex = "tex{$exam->term}";
        $avg = AssessmentRecord::where($d)
            ->where($tex, '>=', 0)
            ->avg($tex);
        return round((float) $avg, 1);
    }

    /**
     * Class average for a given exam.
     *
     * @param mixed $exam
     * @param int   $class_id
     * @param string $year
     * @return float
     */
    public function getClassAverageTermMark($exam, int $class_id, string $year): float
    {
        $d = ['exam_id' => $exam->id, 'my_class_id' => $class_id, 'year' => $year];
        $tex = "tex{$exam->term}";
        $avg = AssessmentRecord::where($d)->select($tex)->avg($tex);
        return round((float) $avg, 1);
    }

    /**
     * Get a specific value from the first assessment record matching conditions.
     *
     * @param array  $where
     * @param string $value  Column name
     * @return mixed
     */
    public function getStudentColumnValue(array $where, string $value): mixed
    {
        return AssessmentRecord::where($where)
            ->select('average')
            ->first()
        ->$value;
    }

    /**
     * Calculate a student's CA position (1‑based) directly via the database,
     * using the sort order supplied by the controller.
     *
     * @param int      $st_id
     * @param int      $exam_id
     * @param int      $class_id
     * @param string   $year
     * @param string   $value        column to compare (e.g., 'total', 'ave')
     * @param string   $sort_order    'asc' (lower is better) or 'desc' (higher is better)
     * @param int|null $section_id   optional section filter
     * @return int|null              position (1‑based) or null if not applicable
     */
    public function getStudentPositionByColumn(
        int $st_id,
        int $exam_id,
        int $class_id,
        string $year,
        string $value,
        string $sort_order = 'desc',
        ?int $section_id = null
    ): ?int {
        $base_query = AssessmentRecord::query()
            ->select('student_id', $value)
            ->where('exam_id', $exam_id)
            ->where('my_class_id', $class_id)
            ->where('year', $year)
            ->whereNotNull($value);

        if ($section_id !== null) {
            $base_query->where('section_id', $section_id);
        }

        $student_record = (clone $base_query)->where('student_id', $st_id)->first();
        if (!$student_record || $student_record->$value === null) {
            return null;
        }
        $student_value = $student_record->$value;

        $operator = ($sort_order === 'asc') ? '<' : '>';
        $better_count = (clone $base_query)
            ->where('student_id', '!=', $st_id)
            ->where($value, $operator, $student_value)
            ->distinct($value)
            ->count($value);

        return $better_count + 1;
    }

    /**
     * Get the list of term totals for a set of records, sorted descending.
     *
     * @param array  $where
     * @param string $tex   Column name like "tex1"
     * @return Collection
     */
    public function getTermTotalsDescending(array $where, string $tex): Collection
    {
        return AssessmentRecord::where($where)
            ->whereNotNull($tex)
            ->orderByDesc($tex)
            ->select($tex)
            ->get()
            ->pluck($tex);
    }

    /**
     * Get the term mark for a specific subject, student, exam, and class.
     *
     * @param mixed  $exam
     * @param int    $class_id
     * @param int    $sub_id
     * @param int    $st_id
     * @param string $year
     * @return float|null
     */
    public function getSubjectTermMark($exam, int $class_id, int $sub_id, int $st_id, string $year): ?float
    {
        $d = [
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'subject_id' => $sub_id,
            'student_id' => $st_id,
            'year' => $year
        ];
        $tex = "tex{$exam->term}";
        $record = AssessmentRecord::where($d)->select($tex)->first();
        return $record ? $record->$tex : null;
    }

    /**
     * Get a student's subject position (rank) within a class.
     *
     * @param int   $st_id
     * @param mixed $exam
     * @param int   $class_id
     * @param int   $sub_id
     * @param string $year
     * @return int|null
     */
    public function getSubjectPosition(int $st_id, $exam, int $class_id, int $sub_id, string $year): ?int
    {
        $d = ['exam_id' => $exam->id, 'my_class_id' => $class_id, 'subject_id' => $sub_id, 'year' => $year];
        $tex = "tex{$exam->term}";

        $sub_mk = $this->getSubjectTermMark($exam, $class_id, $sub_id, $st_id, $year);
        $sub_mks = AssessmentRecord::where($d)
            ->whereNotNull($tex)
            ->orderByDesc($tex)
            ->select($tex)
            ->get()
            ->pluck($tex);

        $position = $sub_mks->search($sub_mk);
        return ($position !== false) ? $position + 1 : null;
    }

    /**
     * Create an assessment record if it doesn't already exist.
     *
     * @param int $sid          student_id
     * @param int $class_id     my_class_id
     * @param int $sec_id       section_id
     * @param int $exam_id
     * @param string $year
     * @param int $sub_id       subject_id
     * @param int $mark_id
     * @param int $assessment_id
     * @return AssessmentRecord
     */
    public function firstOrCreateAssessmentRecord(int $sid, int $class_id, int $sec_id, int $exam_id, string $year, int $sub_id, int $mark_id, int $assessment_id): AssessmentRecord
    {
        return AssessmentRecord::firstOrCreate([
            'student_id' => $sid,
            'my_class_id' => $class_id,
            'section_id' => $sec_id,
            'exam_id' => $exam_id,
            'year' => $year,
            'subject_id' => $sub_id,
            'mark_id' => $mark_id,
            'assessment_id' => $assessment_id,
        ]);
    }
}
