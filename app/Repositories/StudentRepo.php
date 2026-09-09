<?php

namespace App\Repositories;

use App\Helpers\Qs;
use App\Models\Dorm;
use App\Models\Promotion;
use App\Models\StudentRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StudentRepo
{
    /*********** Student record ***************/

    /**
     * Get student records matching conditions, with optional relations.
     */
    public function getStudentRecords(array $where = [], array $with = ['my_class', 'section', 'user']): Collection
    {
        return StudentRecord::where($where)->with($with)->get();
    }

    /**
     * Get a single column value from the first student record matching conditions.
     */
    public static function getStudentRecordColumn(array $where, string $col_name): mixed
    {
        return StudentRecord::where($where)->value($col_name);
    }

    /**
     * Get active students in a specific class, excluding soft‑deleted users, sorted by name.
     */
    public function getActiveStudentsByClass(int $class_id, array $with = ['user']): Collection
    {
        return $this->getActiveStudentsQuery()
            ->where('my_class_id', $class_id)
            ->with($with)
            ->get()
            ->whereNotNull('user')
            ->sortBy('user.name');
    }

    /**
     * Query builder for active (not graduated) student records.
     */
    public function getActiveStudentsQuery(): Builder
    {
        return StudentRecord::where('is_graduated', false);
    }

    /**
     * Query builder for promotions where the student graduated.
     * (The method name is historical; it returns the Promotion model, not StudentRecord.)
     */
    public function getGraduatedPromotionsQuery(): Builder
    {
        return Promotion::where('is_graduated', true);
    }

    /**
     * Get all promotion records for graduated students (raw, no relations).
     */
    public function getAllGraduatedPromotions(): Collection
    {
        return $this->getGraduatedPromotionsQuery()->get();
    }

    /**
     * Get all graduated student promotions with related user, classes, and sections.
     */
    public function getAllGraduatedPromotionsWithDetails(): Collection
    {
        return $this->getGraduatedPromotionsQuery()
            ->with(['student', 'user', 'fc', 'tc', 'fs', 'ts'])
            ->get()
            ->sortBy('user.name');
    }

    /**
     * Create a new student record.
     */
    public function createStudentRecord(array $data): StudentRecord
    {
        return StudentRecord::create($data);
    }

    /**
     * Update student records matching a given column value.
     *
     * @param  mixed  $value  The value to match
     * @param  array  $data  Columns to update
     * @param  string  $column  Column to filter by (default 'id')
     * @return int Number of updated rows
     */
    public function updateStudentRecords($value, array $data, string $column = 'id'): int
    {
        return StudentRecord::where($column, $value)->update($data);
    }

    /**
     * Query builder for active students matching conditions, with optional relations.
     */
    public function getActiveStudentsQueryWithRelations(array $where, array $with = ['user', 'my_parent']): Builder
    {
        return $this->getActiveStudentsQuery()->where($where)->with($with);
    }

    /**
     * Query builder for graduated students matching conditions.
     */
    public function getGraduatedStudentsQuery(array $where, array $with = ['user', 'my_parent']): Builder
    {
        return StudentRecord::where('is_graduated', true)->where($where)->with($with);
    }

    /**
     * Query builder for active student records for a set of user IDs, with user relation (for eager loading).
     */
    public function getActiveStudentsByUserIdsQuery(array $ids): Builder
    {
        return $this->getActiveStudentsQuery()->whereIn('user_id', $ids)->with('user');
    }

    /**
     * Query builder student records (active or graduated) for a set of user IDs, with user relation.
     */
    public function getStudentsByUserIdsQuery(array $ids): Builder
    {
        return StudentRecord::whereIn('user_id', $ids)->with('user');
    }

    /**
     * Get all dormitories ordered by name.
     */
    public function getAllDorms(): Collection
    {
        return Dorm::orderBy('name', 'asc')->get();
    }

    /**
     * Check if a student exists either as an active or graduated student.
     *
     * @param  int  $student_id  (user_id)
     */
    public function exists(int $student_id): bool
    {
        return $this->getActiveStudentsQueryWithRelations(['user_id' => $student_id])->exists()
            || $this->getGraduatedStudentsQuery(['user_id' => $student_id])->exists();
    }

    /************* Promotions *************/

    /**
     * Create a new promotion record.
     */
    public function createPromotion(array $data): Promotion
    {
        return Promotion::create($data);
    }

    /**
     * Update promotion records matching conditions.
     */
    public function updatePromotion(array $where, array $data): int
    {
        return Promotion::where($where)->update($data);
    }

    /**
     * Find a promotion by its primary key.
     */
    public function findPromotion(int $id): ?Promotion
    {
        return Promotion::find($id);
    }

    /**
     * Delete a promotion by its primary key.
     *
     * @return int Number of deleted rows (0 or 1)
     */
    public function deletePromotion(int $id): int
    {
        return Promotion::destroy($id);
    }

    /**
     * Get all promotions for the current session → next session transition.
     */
    public function getAllPromotions(): Collection
    {
        return Promotion::with(['student', 'fc', 'tc', 'fs', 'ts'])
            ->where('from_session', Qs::getCurrentSession())
            ->where('to_session', Qs::getNextSession())
            ->get();
    }

    /**
     * Get promotions matching specific conditions.
     */
    public function getPromotionsByConditions(array $where): Collection
    {
        return Promotion::where($where)->get();
    }
}
