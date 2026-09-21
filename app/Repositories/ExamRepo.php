<?php

namespace App\Repositories;

use App\Models\ClassType;
use App\Models\Division;
use App\Models\Exam;
use App\Models\ExamAnnounce;
use App\Models\ExamCategory;
use App\Models\ExamHasAssessment;
use App\Models\ExamNumberFormat;
use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\Section;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExamRepo
{
    public function __construct(protected string $marks_table = 'marks', protected string $exam_records_table = 'exam_records') {}

    /*********** Exam category ***************/

    /**
     * Get all exam categories.
     */
    public function getCategories(): Collection
    {
        return ExamCategory::get();
    }

    /*********** Exam Announce ***************/

    /**
     * Create or get an existing exam announcement.
     */
    public function announce(array $data): ExamAnnounce
    {
        return ExamAnnounce::firstOrCreate($data);
    }

    /*********** Exam number format ***************/

    /**
     * Create a new exam number format if it doesn't exist.
     */
    public function createNumberFormat(array $data): ExamNumberFormat
    {
        return ExamNumberFormat::firstOrCreate($data);
    }

    /**
     * Update an exam number format by ID.
     */
    public function updateNumberFormat(int $id, array $data): int
    {
        return ExamNumberFormat::where('id', $id)->update($data);
    }

    /**
     * Update or create an exam number format.
     */
    public function updateOrCreateNumberFormat(array $attributes, array $values): ExamNumberFormat
    {
        return ExamNumberFormat::updateOrCreate($attributes, $values);
    }

    /**
     * Delete an exam number format by ID.
     */
    public function deleteNumberFormat(int $id): ?bool
    {
        return ExamNumberFormat::find($id)?->delete();
    }

    /*********** Exam ***************/

    /**
     * Get only published exams with optional relations.
     */
    public function getPublishedExams(array $with = ['category']): Collection
    {
        return Exam::where('is_published', true)
            ->orderByDesc('created_at')
            ->orderByDesc('year')
            ->with($with)
            ->get();
    }

    /**
     * Get published exams filtered by given conditions.
     */
    public function getPublished(array $where): Collection
    {
        return Exam::where($where)->where('is_published', true)->get();
    }

    /**
     * Get all exams with optional relations.
     */
    public function getAllExams(array $with = ['category', 'class_type']): Collection
    {
        return Exam::orderByDesc('created_at')->orderByDesc('year')->with($with)->get();
    }

    /**
     * Get an exam by ID, optionally restricting to published ones.
     */
    public function findExam(int $id, bool $only_published = true, array $with = ['category', 'class_type']): ?Exam
    {
        $query = Exam::where('id', $id)->with($with)
            ->when($only_published, fn ($query) => $query->where('is_published', true));

        return $query->first();
    }

    /**
     * Get exams matching conditions, with optional filtering of locked ones.
     */
    public function getExamsByConditions(array $where, bool $without_locked_ones = true, array $with = []): Collection
    {
        $query = Exam::where($where)->latest()->with($with);
        if ($without_locked_ones) {
            $query->where('is_locked', false);
        }

        return $query->get();
    }

    /**
     * Check if an exam exists by ID.
     */
    public function exists(int $exam_id): bool
    {
        return Exam::where('id', $exam_id)->exists();
    }

    /**
     * Get soft-deleted exams.
     */
    public function getTrashed(array $with = ['category']): Collection
    {
        return Exam::onlyTrashed()->with($with)->get();
    }

    /**
     * Check if there is at least one exam.
     */
    public function hasAnyExam(): bool
    {
        return Exam::exists();
    }

    /**
     * Find an exam by its primary key.
     */
    public function find(int $id): ?Exam
    {
        return Exam::find($id);
    }

    /**
     * Find an exam by attributes or create a new one.
     */
    public function firstOrCreate(array $data): Exam
    {
        return Exam::firstOrCreate($data);
    }

    /**
     * Get published exams ordered by latest.
     */
    public function getLatestPublished(): Collection
    {
        return Exam::latest()->where('is_published', true)->get();
    }

    /**
     * Count published exams.
     */
    public function countPublishedExams(): int
    {
        return Exam::where('is_published', true)->count();
    }

    /**
     * Update an exam by ID.
     */
    public function update(int $id, array $data): int
    {
        return Exam::where('id', $id)->update($data);
    }

    /**
     * Soft-delete an exam by ID.
     */
    public function delete(int $id): int
    {
        return Exam::destroy($id);
    }

    /**
     * Restore a soft-deleted exam by ID.
     */
    public function restore(int $id): int
    {
        return Exam::where('id', $id)->restore();
    }

    /**
     * Permanently delete an exam.
     */
    public function forceDelete(int $id): int
    {
        return Exam::where('id', $id)->forceDelete();
    }

    /**
     * Get distinct years from exams.
     */
    public function getDistinctExamYears(): Collection
    {
        return Exam::select('year')->distinct()->get();
    }

    /*********** Exam Record ***************/

    /**
     * Create an exam record if it doesn't exist.
     */
    public function firstOrCreateExamRecord(array $data): ExamRecord
    {
        return ExamRecord::firstOrCreate($data);
    }

    /**
     * Update or create an exam record.
     */
    public function updateOrCreateExamRecord(array $attributes, array $values): ExamRecord
    {
        return ExamRecord::updateOrCreate($attributes, $values);
    }

    /**
     * Delete exam records matching conditions.
     */
    public function deleteExamRecords(array $where): int
    {
        return ExamRecord::where($where)->delete();
    }

    /**
     * Update exam records matching conditions.
     */
    public function updateExamRecords(array $where, array $data): int
    {
        return ExamRecord::where($where)->update($data);
    }

    /**
     * Mass upsert exam records.
     */
    public function massUpdateExamRecords(array $values, array $unique_by = ['id']): int|Builder|ExamRecord
    {
        return ExamRecord::massUpdate(
            values: $values,
            uniqueBy: $unique_by
        );
    }

    /**
     * Get exam records with optional relations.
     */
    public function getExamRecords(array $where, array $with = []): Collection
    {
        return ExamRecord::where($where)->with($with)->get();
    }

    /**
     * Get exam records for a specific student.
     *
     * @param  int  $id  student_id
     */
    public function getExamRecordsByStudent(int $id): Builder
    {
        return ExamRecord::with('exam')->with('student')->where('student_id', $id);
    }

    /**
     * Get exam records for a specific exam (with class relation).
     */
    public function getExamRecordsByExam(int $exam_id): Builder
    {
        return ExamRecord::with('my_class')->where('exam_id', $exam_id);
    }

    /**
     * Get exam records for multiple students (with exam and student).
     *
     * @param  array  $ids  student IDs
     */
    public function getExamRecordsByStudents(array $ids): Builder
    {
        return ExamRecord::with('exam')->with('student')->whereIn('student_id', $ids);
    }

    /**
     * Calculate a student's position (1‑based) directly via the database,
     * using the sort order supplied.
     *
     * @param  string  $value  column to compare (e.g., 'total', 'ave', 'gpa', 'points')
     * @param  string  $sort_order  'asc' (lower is better) or 'desc' (higher is better)
     * @param  int|null  $section_id  optional section filter
     * @return int|null position (1‑based) or null if not applicable
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
        $base_query = ExamRecord::query()
            ->select('student_id', $value)
            ->where('exam_id', $exam_id)
            ->where('my_class_id', $class_id)
            ->where('year', $year)
            ->whereNotNull($value);

        if ($section_id !== null) {
            $base_query->where('section_id', $section_id);
        }

        $student_record = (clone $base_query)->where('student_id', $st_id)->first();
        if (! $student_record || $student_record->$value === null) {
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
     * Recompute section_position OR class_position on exam_records
     * in one DENSE_RANK() window query.
     *
     * DENSE_RANK gives tied students the same position and no gaps,
     * which is exactly what the old COUNT(DISTINCT better) + 1 logic did.
     */
    public function refreshStudentPositions(
        int $exam_id,
        int $class_id,
        string $year,
        string $orderByColumn,
        string $sortOrder = 'desc',
        bool $bySection = true,
        ?int $section_id = null
    ): int {
        $partition = $bySection ? 'section_id' : 'my_class_id';
        $positionColumn = $bySection ? 'section_position' : 'class_position';
        $direction = strtolower($sortOrder) === 'asc' ? 'ASC' : 'DESC';

        $query = DB::table($this->exam_records_table)
            ->select('id')
            ->selectRaw(
                "DENSE_RANK() OVER (PARTITION BY {$partition} ORDER BY {$orderByColumn} {$direction}) AS pos"
            )
            ->where('exam_id', $exam_id)
            ->where('my_class_id', $class_id)
            ->where('year', $year)
            ->whereNotNull($orderByColumn);

        if ($section_id !== null) {
            $query->where('section_id', $section_id);
        }

        $rows = $query->get()
            ->map(fn ($r) => ['id' => $r->id, $positionColumn => $r->pos])
            ->all();

        return empty($rows)
            ? 0
            : ExamRecord::massUpdate(values: $rows, uniqueBy: ['id']);
    }

    /**
     * Compute every per-student aggregate needed for exam_records
     * in a single CTE using window functions.
     *
     * Returns a Collection of stdClass with columns:
     *   student_id, my_class_id, section_id, exam_id, year,
     *   total, average, points, total_credits, total_credits_point
     *
     * The caller fills in grade_id, division_id and class_average
     * from already-loaded lookups — no extra round-trips.
     */
    public function fetchExamRecordAggregates(
        int $exam_id,
        int $class_id,
        string $year,
        int $term,
        int $min_subjects
    ): \Illuminate\Support\Collection {
        $tex = "tex{$term}";

        // MySQL: ORDER BY x ASC puts NULL first. PG/SQLite: NULLS LAST.
        // Using a boolean lead-sort key keeps the syntax identical on all three.
        $nullsFirst = DB::connection()->getDriverName() === 'mysql';

        $nullKeyPoint = $nullsFirst
            ? 'CASE WHEN grade_point IS NULL THEN 0 ELSE 1 END ASC, grade_point ASC'
            : 'grade_point ASC NULLS LAST';
        $nullKeyCredit = 'credits ASC';

        $sql = <<<SQL
        WITH marks_enriched AS (
            SELECT
                m.student_id,
                m.my_class_id,
                m.section_id,
                m.exam_id,
                m.year,
                m.{$tex} AS mark_value,
                s.is_core,
                s.credits,
                g.point AS grade_point
            FROM marks m
            LEFT JOIN subjects s ON s.id = m.subject_id
            LEFT JOIN grades g ON g.id = m.grade_id
            WHERE m.exam_id     = ?
              AND m.my_class_id = ?
              AND m.year        = ?
              AND m.{$tex}      IS NOT NULL
              AND g.is_active   = 1
        ),
        ranked AS (
            SELECT *,
                ROW_NUMBER() OVER (
                    PARTITION BY student_id
                    ORDER BY CASE WHEN is_core THEN mark_value END DESC
                ) AS core_rank,
                ROW_NUMBER() OVER (
                    PARTITION BY student_id
                    ORDER BY {$nullKeyPoint}
                ) AS point_rank,
                ROW_NUMBER() OVER (
                    PARTITION BY student_id
                    ORDER BY {$nullKeyCredit}
                ) AS credit_rank
            FROM marks_enriched
        ),
        core_ranked AS (
            SELECT *,
                ROW_NUMBER() OVER (
                    PARTITION BY student_id
                    ORDER BY grade_point * credits ASC
                ) AS cp_rank
            FROM marks_enriched
            WHERE is_core = 1
        ),
        core_credits AS (
            SELECT student_id,
                   SUM(grade_point * credits) AS total_credits_point
            FROM core_ranked
            WHERE cp_rank <= {$min_subjects}
            GROUP BY student_id
        )
        SELECT
            r.student_id,
            r.my_class_id,
            r.section_id,
            r.exam_id,
            r.year,
            SUM(r.mark_value) AS total,
            CASE
                WHEN COUNT(*) < {$min_subjects} THEN 0
                ELSE COALESCE(
                    AVG(CASE WHEN r.is_core = 1 AND r.core_rank <= {$min_subjects}
                             THEN r.mark_value END),
                    0
                )
            END AS average,
            SUM(CASE WHEN r.point_rank <= {$min_subjects}
                     THEN r.grade_point ELSE 0 END) AS points,
            SUM(CASE WHEN r.credit_rank <= {$min_subjects}
                     THEN r.credits ELSE 0 END) AS total_credits,
            COALESCE(cc.total_credits_point, 0) AS total_credits_point
        FROM ranked r
        LEFT JOIN core_credits cc ON cc.student_id = r.student_id
        GROUP BY r.student_id, r.my_class_id, r.section_id, r.exam_id, r.year
        SQL;

        return collect(DB::select($sql, [$exam_id, $class_id, $year]));
    }

    /*********** Grades ***************/

    /**
     * Get all grades ordered by class type and name.
     */
    public function getAllGrades(bool $include_historic = false): Collection
    {
        $query = Grade::query()->orderBy('class_type_id')->orderBy('name');

        if (! $include_historic) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Get grades matching conditions with optional relations.
     */
    public function getGradesByConditions(array $data, array $with = []): Collection
    {
        return Grade::where($data)->with($with)->get();
    }

    /**
     * Find a grade by ID.
     */
    public function findGradeById(int $id): ?Grade
    {
        return Grade::find($id);
    }

    /**
     * Create a new grade.
     */
    public function createGrade(array $data): Grade
    {
        return Grade::create($data);
    }

    public function updateGrade(int $id, array $data): Grade
    {
        $grade = Grade::findOrFail($id);

        if (! $this->gradeHasChanged($grade, $data)) {
            return $grade;
        }

        if (! $grade->is_locked) {
            $grade->update($data);

            return $grade;
        }

        if ($this->gradeIsUsedByInProgressExam($grade)) {
            throw new \DomainException(__('msg.grade_locked_by_in_progress_exam'));
        }

        $new = Grade::create([
            'class_type_id' => $grade->class_type_id,
            'name' => $data['name'] ?? $grade->name,
            'mark_from' => $data['mark_from'] ?? $grade->mark_from,
            'mark_to' => $data['mark_to'] ?? $grade->mark_to,
            'point' => $data['point'] ?? $grade->point,
            'remarks' => $data['remarks'] ?? $grade->remarks,
            'is_locked' => false,
            'is_active' => true,
        ]);

        $grade->update(['is_active' => false]);

        return $new;
    }

    protected function gradeHasChanged(Grade $grade, array $data): bool
    {
        return ($data['name'] ?? $grade->name) !== $grade->name
            || ($data['mark_from'] ?? $grade->mark_from) != $grade->mark_from
            || ($data['mark_to'] ?? $grade->mark_to) != $grade->mark_to
            || ($data['point'] ?? $grade->point) != $grade->point
            || ($data['remarks'] ?? $grade->remarks) !== $grade->remarks;
    }

    protected function gradeIsUsedByInProgressExam(Grade $grade): bool
    {
        return Mark::where('grade_id', $grade->id)
            ->whereHas('exam', fn ($q) => $q->where('is_published', false))
            ->exists()
            || ExamRecord::where('grade_id', $grade->id)
                ->whereHas('exam', fn ($q) => $q->where('is_published', false))
                ->exists();
    }

    /**
     * Delete a grade by ID.
     */
    public function deleteGrade(int $id): int
    {
        $grade = Grade::findOrFail($id);

        if ($grade->is_locked) {
            throw new \DomainException(__('msg.cannot_delete_locked_grade'));
        }

        return $grade->delete();
    }

    /*********** Marks ***************/

    /**
     * Create a mark if it doesn't exist.
     */
    public function firstOrCreateMark(array $data): Mark
    {
        return Mark::firstOrCreate($data);
    }

    /**
     * Delete marks matching conditions.
     */
    public function deleteMarksByConditions(array $where): int
    {
        return Mark::where($where)->delete();
    }

    /**
     * Update a mark by ID.
     */
    public function updateMark(int $id, array $data): int
    {
        return Mark::where('id', $id)->update($data);
    }

    /**
     * Mass upsert marks.
     */
    public function massUpdateMark(array $values, array $unique_by = ['id']): int|Builder|Mark
    {
        return Mark::massUpdate(
            values: $values,
            uniqueBy: $unique_by
        );
    }

    /**
     * Get distinct exam years for a student from marks.
     */
    public function getDistinctMarkYears(int $student_id): Collection
    {
        return Mark::where('student_id', $student_id)
            ->select('year')
            ->distinct()
            ->get();
    }

    /**
     * Get marks matching conditions with optional relations.
     */
    public function getMarksByConditions(array $where, array $with = ['grade', 'user', 'subject', 'section']): Collection
    {
        return Mark::where($where)->with($with)->get();
    }

    /**
     * Get marks for a set of student IDs.
     */
    public function getMarksByStudentIds(array $ids): Builder|Mark
    {
        return Mark::whereIn('student_id', $ids);
    }

    /**
     * Lock every grade and division used by the given exam.
     * Called after an exam is published.
     */
    public function lockGradesAndDivisionsForExam(int $exam_id): void
    {
        $grade_ids = Mark::where('exam_id', $exam_id)
            ->whereNotNull('grade_id')
            ->distinct()
            ->pluck('grade_id')
            ->merge(
                ExamRecord::where('exam_id', $exam_id)
                    ->whereNotNull('grade_id')
                    ->distinct()
                    ->pluck('grade_id')
            )
            ->unique();

        if ($grade_ids->isNotEmpty()) {
            Grade::whereIn('id', $grade_ids)->update(['is_locked' => true]);
        }

        $division_ids = ExamRecord::where('exam_id', $exam_id)
            ->whereNotNull('division_id')
            ->distinct()
            ->pluck('division_id');

        if ($division_ids->isNotEmpty()) {
            Division::whereIn('id', $division_ids)->update(['is_locked' => true]);
        }
    }

    /**
     * Get all active grades for a class type.
     */
    public function getActiveGrades(int $class_type_id): Collection
    {
        return Grade::where('class_type_id', $class_type_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all active divisions for a class type.
     */
    public function getActiveDivisions(int $class_type_id): Collection
    {
        return Division::where('class_type_id', $class_type_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all grades (used for marks context).
     */
    public function getGrades(): Collection
    {
        return Grade::active()->get();
    }

    /*********** Skills ***************/

    /**
     * Get skills matching conditions.
     */
    public function getSkills(array $where): Collection
    {
        return Skill::where($where)->orderBy('name')->get();
    }

    /**
     * Get skills for a class type, optionally filtered by skill type.
     */
    public function getSkillsByClassType(?int $class_type_id = null, ?string $skill_type = null): Collection
    {
        $where = ['class_type_id' => $class_type_id];
        if ($skill_type) {
            $where['skill_type'] = $skill_type;
        }

        return $this->getSkills($where);
    }

    /*********** Sections ***************/

    /**
     * Get sections matching conditions.
     */
    public function getSectionsByConditions(array $where): Collection
    {
        return Section::where($where)->get();
    }

    /*********** Divisions ***************/

    /**
     * Get all divisions ordered by class type and name.
     */
    public function getAllDivisions(bool $include_historic = false): Collection
    {
        $query = Division::orderBy('class_type_id')->orderBy('name');

        if (! $include_historic) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Get divisions matching conditions with optional relations.
     */
    public function getDivisionsByConditions(array $where, array $with): Collection
    {
        return Division::where($where)->with($with)->get();
    }

    /**
     * Create a new division.
     */
    public function createDivision(array $data): Division
    {
        return Division::create($data);
    }

    /**
     * Update a division by ID.
     */
    public function updateDivision(int $id, array $data): Division
    {
        $division = Division::findOrFail($id);

        if (! $this->divisionHasChanged($division, $data)) {
            return $division;
        }

        if (! $division->is_locked) {
            $division->update($data);

            return $division;
        }

        if ($this->divisionIsUsedByInProgressExam($division)) {
            throw new \DomainException(__('msg.grade_locked_by_in_progress_exam'));
        }

        $new = Division::create([
            'class_type_id' => $division->class_type_id,
            'name' => $data['name'] ?? $division->name,
            'mark_from' => $data['mark_from'] ?? $division->mark_from,
            'mark_to' => $data['mark_to'] ?? $division->mark_to,
            'point' => $data['point'] ?? $division->point,
            'remarks' => $data['remarks'] ?? $division->remarks,
            'is_locked' => false,
            'is_active' => true,
        ]);

        $division->update(['is_active' => false]);

        return $new;
    }

    protected function divisionHasChanged(Division $division, array $data): bool
    {
        return ($data['name'] ?? $division->name) !== $division->name
            || ($data['mark_from'] ?? $division->mark_from) != $division->mark_from
            || ($data['mark_to'] ?? $division->mark_to) != $division->mark_to
            || ($data['point'] ?? $division->point) != $division->point
            || ($data['remarks'] ?? $division->remarks) !== $division->remarks;
    }

    protected function divisionIsUsedByInProgressExam(Division $division): bool
    {
        return Mark::where('grade_id', $division->id)
            ->whereHas('exam', fn ($q) => $q->where('is_published', false))
            ->exists()
            || ExamRecord::where('grade_id', $division->id)
                ->whereHas('exam', fn ($q) => $q->where('is_published', false))
                ->exists();
    }

    /**
     * Delete a division by ID.
     */
    public function deleteDivision(int $id): int
    {
        $division = Division::findOrFail($id);

        if ($division->is_locked) {
            throw new \DomainException(__('msg.cannot_delete_locked_division'));
        }

        return $division->delete();
    }

    /*********** Class types ***************/

    /**
     * Get all class types.
     */
    public function getAllClassTypes(): Collection
    {
        return ClassType::get();
    }

    /*********** Exam has Assessments ***************/

    /**
     * Create or find an exam‑assessment association.
     */
    public function firstOrCreateExamAssessment(array $data): ExamHasAssessment
    {
        return ExamHasAssessment::firstOrCreate($data);
    }
}
