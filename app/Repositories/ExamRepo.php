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

class ExamRepo
{
    protected string $marks_table;

    protected string $exam_records_table;

    public function __construct()
    {
        $this->marks_table = 'marks';
        $this->exam_records_table = 'exam_records';
    }

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
    public function massUpdateExamRecords(array $values, array $unique_by = ['id']): int
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

    /*********** Grades ***************/

    /**
     * Get all grades ordered by class type and name.
     */
    public function getAllGrades(): Collection
    {
        return Grade::orderBy('class_type_id')->orderBy('name')->get();
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

    /**
     * Update a grade by ID.
     */
    public function updateGrade(int $id, array $data): int
    {
        return Grade::where('id', $id)->update($data);
    }

    /**
     * Delete a grade by ID.
     */
    public function deleteGrade(int $id): int
    {
        return Grade::destroy($id);
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
    public function massUpdateMark(array $values, array $unique_by = ['id']): int
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
    public function getMarksByStudentIds(array $ids): Builder
    {
        return Mark::whereIn('student_id', $ids);
    }

    /**
     * Get all grades (used for marks context).
     */
    public function getGrades(): Collection
    {
        return Grade::get();
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
    public function getAllDivisions(): Collection
    {
        return Division::orderBy('class_type_id')->orderBy('name')->get();
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
    public function updateDivision(int $id, array $data): int
    {
        return Division::where('id', $id)->update($data);
    }

    /**
     * Delete a division by ID.
     */
    public function deleteDivision(int $id): int
    {
        return Division::destroy($id);
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
