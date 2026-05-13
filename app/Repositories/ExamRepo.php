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
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return ExamCategory::get();
    }

    /*********** Exam Announce ***************/

    /**
     * Create or get an existing exam announcement.
     *
     * @param array $data
     * @return ExamAnnounce
     */
    public function announce(array $data): ExamAnnounce
    {
        return ExamAnnounce::firstOrCreate($data);
    }

    /*********** Exam number format ***************/

    /**
     * Create a new exam number format if it doesn't exist.
     *
     * @param array $data
     * @return ExamNumberFormat
     */
    public function createNumberFormat(array $data): ExamNumberFormat
    {
        return ExamNumberFormat::firstOrCreate($data);
    }

    /**
     * Update an exam number format by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateNumberFormat(int $id, array $data): int
    {
        return ExamNumberFormat::where('id', $id)->update($data);
    }

    /**
     * Update or create an exam number format.
     *
     * @param array $attributes
     * @param array $values
     * @return ExamNumberFormat
     */
    public function updateOrCreateNumberFormat(array $attributes, array $values): ExamNumberFormat
    {
        return ExamNumberFormat::updateOrCreate($attributes, $values);
    }

    /**
     * Delete an exam number format by ID.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteNumberFormat(int $id): ?bool
    {
        return ExamNumberFormat::find($id)?->delete();
    }

    /*********** Exam ***************/

    /**
     * Get only published exams with optional relations.
     *
     * @param array $with
     * @return Collection
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
     *
     * @param array $where
     * @return Collection
     */
    public function getPublished(array $where): Collection
    {
        return Exam::where($where)->where('is_published', true)->get();
    }

    /**
     * Get all exams with optional relations.
     *
     * @param array $with
     * @return Collection
     */
    public function getAllExams(array $with = ['category', 'class_type']): Collection
    {
        return Exam::orderByDesc('created_at')->orderByDesc('year')->with($with)->get();
    }

    /**
     * Get an exam by ID, optionally restricting to published ones.
     *
     * @param int   $id
     * @param bool  $only_published
     * @param array $with
     * @return Exam|null
     */
    public function findExam(int $id, bool $only_published = true, array $with = ['category', 'class_type']): ?Exam
    {
        $query = Exam::where('id', $id)->with($with)
            ->when($only_published, fn($query) => $query->where('is_published', true));

        return $query->first();
    }

    /**
     * Get exams matching conditions, with optional filtering of locked ones.
     *
     * @param array $where
     * @param bool  $without_locked_ones
     * @param array $with
     * @return Collection
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
     *
     * @param int $exam_id
     * @return bool
     */
    public function exists(int $exam_id): bool
    {
        return Exam::where('id', $exam_id)->exists();
    }

    /**
     * Get soft-deleted exams.
     *
     * @param array $with
     * @return Collection
     */
    public function getTrashed(array $with = ['category']): Collection
    {
        return Exam::onlyTrashed()->with($with)->get();
    }

    /**
     * Check if there is at least one exam.
     *
     * @return bool
     */
    public function hasAnyExam(): bool
    {
        return Exam::exists();
    }

    /**
     * Find an exam by its primary key.
     *
     * @param int $id
     * @return Exam|null
     */
    public function find(int $id): ?Exam
    {
        return Exam::find($id);
    }

    /**
     * Find an exam by attributes or create a new one.
     *
     * @param array $data
     * @return Exam
     */
    public function firstOrCreate(array $data): Exam
    {
        return Exam::firstOrCreate($data);
    }

    /**
     * Get published exams ordered by latest.
     *
     * @return Collection
     */
    public function getLatestPublished(): Collection
    {
        return Exam::latest()->where('is_published', true)->get();
    }

    /**
     * Count published exams.
     *
     * @return int
     */
    public function countPublishedExams(): int
    {
        return Exam::where('is_published', true)->count();
    }

    /**
     * Update an exam by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int
    {
        return Exam::where('id', $id)->update($data);
    }

    /**
     * Soft-delete an exam by ID.
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return Exam::destroy($id);
    }

    /**
     * Restore a soft-deleted exam by ID.
     *
     * @param int $id
     * @return int
     */
    public function restore(int $id): int
    {
        return Exam::where('id', $id)->restore();
    }

    /**
     * Permanently delete an exam.
     *
     * @param int $id
     * @return int
     */
    public function forceDelete(int $id): int
    {
        return Exam::where('id', $id)->forceDelete();
    }

    /**
     * Get distinct years from exams.
     *
     * @return Collection
     */
    public function getDistinctExamYears(): Collection
    {
        return Exam::select('year')->distinct()->get();
    }

    /*********** Exam Record ***************/

    /**
     * Create an exam record if it doesn't exist.
     *
     * @param array $data
     * @return ExamRecord
     */
    public function firstOrCreateExamRecord(array $data): ExamRecord
    {
        return ExamRecord::firstOrCreate($data);
    }

    /**
     * Delete exam records matching conditions.
     *
     * @param array $where
     * @return int
     */
    public function deleteExamRecords(array $where): int
    {
        return ExamRecord::where($where)->delete();
    }

    /**
     * Update exam records matching conditions.
     *
     * @param array $where
     * @param array $data
     * @return int
     */
    public function updateExamRecords(array $where, array $data): int
    {
        return ExamRecord::where($where)->update($data);
    }

    /**
     * Mass upsert exam records.
     *
     * @param array $values
     * @param array $unique_by
     * @return int
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
     *
     * @param array $where
     * @param array $with
     * @return Collection
     */
    public function getExamRecords(array $where, array $with = []): Collection
    {
        return ExamRecord::where($where)->with($with)->get();
    }

    /**
     * Get exam records for a specific student.
     *
     * @param int $id  student_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getExamRecordsByStudent(int $id): \Illuminate\Database\Eloquent\Builder
    {
        return ExamRecord::with('exam')->with('student')->where('student_id', $id);
    }

    /**
     * Get exam records for a specific exam (with class relation).
     *
     * @param int $exam_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getExamRecordsByExam(int $exam_id): \Illuminate\Database\Eloquent\Builder
    {
        return ExamRecord::with('my_class')->where('exam_id', $exam_id);
    }

    /**
     * Get exam records for multiple students (with exam and student).
     *
     * @param array $ids  student IDs
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getExamRecordsByStudents(array $ids): \Illuminate\Database\Eloquent\Builder
    {
        return ExamRecord::with('exam')->with('student')->whereIn('student_id', $ids);
    }

    /**
     * Get a single column value from the first exam record matching conditions.
     *
     * @param array  $where
     * @param string $value
     * @return mixed|null
     */
    public function getExamRecordColumn(array $where, string $value): mixed
    {
        $record = ExamRecord::where($where)->select($value)->first();
        return $record ? $record->$value : null;
    }

    /**
     * Calculate a student's position (1‑based) directly via the database,
     * using the sort order supplied.
     *
     * @param int      $st_id
     * @param int      $exam_id
     * @param int      $class_id
     * @param string   $year
     * @param string   $value        column to compare (e.g., 'total', 'ave', 'gpa', 'points')
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

    /*********** Grades ***************/

    /**
     * Get all grades ordered by class type and name.
     *
     * @return Collection
     */
    public function getAllGrades(): Collection
    {
        return Grade::orderBy('class_type_id')->orderBy('name')->get();
    }

    /**
     * Get grades matching conditions with optional relations.
     *
     * @param array $data
     * @param array $with
     * @return Collection
     */
    public function getGradesByConditions(array $data, array $with = []): Collection
    {
        return Grade::where($data)->with($with)->get();
    }

    /**
     * Find a grade by ID.
     *
     * @param int $id
     * @return Grade|null
     */
    public function findGradeById(int $id): ?Grade
    {
        return Grade::find($id);
    }

    /**
     * Create a new grade.
     *
     * @param array $data
     * @return Grade
     */
    public function createGrade(array $data): Grade
    {
        return Grade::create($data);
    }

    /**
     * Update a grade by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateGrade(int $id, array $data): int
    {
        return Grade::where('id', $id)->update($data);
    }

    /**
     * Delete a grade by ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteGrade(int $id): int
    {
        return Grade::destroy($id);
    }

    /*********** Marks ***************/

    /**
     * Create a mark if it doesn't exist.
     *
     * @param array $data
     * @return Mark
     */
    public function firstOrCreateMark(array $data): Mark
    {
        return Mark::firstOrCreate($data);
    }

    /**
     * Delete marks matching conditions.
     *
     * @param array $where
     * @return int
     */
    public function deleteMarksByConditions(array $where): int
    {
        return Mark::where($where)->delete();
    }

    /**
     * Update a mark by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateMark(int $id, array $data): int
    {
        return Mark::where('id', $id)->update($data);
    }

    /**
     * Mass upsert marks.
     *
     * @param array $values
     * @param array $unique_by
     * @return int
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
     *
     * @param int $student_id
     * @return Collection
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
     *
     * @param array $where
     * @param array $with
     * @return Collection
     */
    public function getMarksByConditions(array $where, array $with = ['grade', 'user', 'subject', 'section']): Collection
    {
        return Mark::where($where)->with($with)->get();
    }

    /**
     * Get marks for a set of student IDs.
     *
     * @param array $ids
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getMarksByStudentIds(array $ids): \Illuminate\Database\Eloquent\Builder
    {
        return Mark::whereIn('student_id', $ids);
    }

    /**
     * Get all grades (used for marks context).
     *
     * @return Collection
     */
    public function getGrades(): Collection
    {
        return Grade::get();
    }

    /*********** Skills ***************/

    /**
     * Get skills matching conditions.
     *
     * @param array $where
     * @return Collection
     */
    public function getSkills(array $where): Collection
    {
        return Skill::where($where)->orderBy('name')->get();
    }

    /**
     * Get skills for a class type, optionally filtered by skill type.
     *
     * @param int|null    $class_type_id
     * @param string|null $skill_type
     * @return Collection
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
     *
     * @param array $where
     * @return Collection
     */
    public function getSectionsByConditions(array $where): Collection
    {
        return Section::where($where)->get();
    }

    /*********** Divisions ***************/

    /**
     * Get all divisions ordered by class type and name.
     *
     * @return Collection
     */
    public function getAllDivisions(): Collection
    {
        return Division::orderBy('class_type_id')->orderBy('name')->get();
    }

    /**
     * Get divisions matching conditions with optional relations.
     *
     * @param array $where
     * @param array $with
     * @return Collection
     */
    public function getDivisionsByConditions(array $where, array $with): Collection
    {
        return Division::where($where)->with($with)->get();
    }

    /**
     * Create a new division.
     *
     * @param array $data
     * @return Division
     */
    public function createDivision(array $data): Division
    {
        return Division::create($data);
    }

    /**
     * Update a division by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateDivision(int $id, array $data): int
    {
        return Division::where('id', $id)->update($data);
    }

    /**
     * Delete a division by ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteDivision(int $id): int
    {
        return Division::destroy($id);
    }

    /*********** Class types ***************/

    /**
     * Get all class types.
     *
     * @return Collection
     */
    public function getAllClassTypes(): Collection
    {
        return ClassType::get();
    }

    /*********** Exam has Assessments ***************/

    /**
     * Create or find an exam‑assessment association.
     *
     * @param array $data
     * @return ExamHasAssessment
     */
    public function firstOrCreateExamAssessment(array $data): ExamHasAssessment
    {
        return ExamHasAssessment::firstOrCreate($data);
    }
}
