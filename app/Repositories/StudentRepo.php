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
     *
     * @param array $where
     * @param array $with
     * @return Collection
     */
    public function getStudentRecords(array $where = [], array $with = ['my_class', 'section', 'user']): Collection
    {
        return StudentRecord::where($where)->with($with)->get();
    }

    /**
     * Get a single column value from the first student record matching conditions.
     *
     * @param array  $where
     * @param string $col_name
     * @return mixed
     */
    public static function getStudentRecordColumn(array $where, string $col_name): mixed
    {
        return StudentRecord::where($where)->value($col_name);
    }

    /**
     * Get active students in a specific class, excluding soft‑deleted users, sorted by name.
     *
     * @param int   $class_id
     * @param array $with
     * @return Collection
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
     *
     * @return Builder
     */
    public function getActiveStudentsQuery(): Builder
    {
        return StudentRecord::where('is_graduated', false);
    }

    /**
     * Query builder for promotions where the student graduated.
     * (The method name is historical; it returns the Promotion model, not StudentRecord.)
     *
     * @return Builder
     */
    public function getGraduatedPromotionsQuery(): Builder
    {
        return Promotion::where('is_graduated', true);
    }

    /**
     * Get all promotion records for graduated students (raw, no relations).
     *
     * @return Collection
     */
    public function getAllGraduatedPromotions(): Collection
    {
        return $this->getGraduatedPromotionsQuery()->get();
    }

    /**
     * Get all graduated student promotions with related user, classes, and sections.
     *
     * @return Collection
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
     *
     * @param array $data
     * @return StudentRecord
     */
    public function createStudentRecord(array $data): StudentRecord
    {
        return StudentRecord::create($data);
    }

    /**
     * Update student records matching a given column value.
     *
     * @param mixed  $value   The value to match
     * @param array  $data    Columns to update
     * @param string $column  Column to filter by (default 'id')
     * @return int            Number of updated rows
     */
    public function updateStudentRecords($value, array $data, string $column = 'id'): int
    {
        return StudentRecord::where($column, $value)->update($data);
    }

    /**
     * Query builder for active students matching conditions, with optional relations.
     *
     * @param array $where
     * @param array $with
     * @return Builder
     */
    public function getActiveStudentsQueryWithRelations(array $where, array $with = ['user', 'my_parent']): Builder
    {
        return $this->getActiveStudentsQuery()->where($where)->with($with);
    }

    /**
     * Query builder for graduated students matching conditions.
     *
     * @param array $where
     * @param array $with
     * @return Builder
     */
    public function getGraduatedStudentsQuery(array $where, array $with = ['user', 'my_parent']): Builder
    {
        return StudentRecord::where('is_graduated', true)->where($where)->with($with);
    }

    /**
     * Query builder for active student records for a set of user IDs, with user relation (for eager loading).
     *
     * @param array $ids
     * @return Builder
     */
    public function getActiveStudentsByUserIdsQuery(array $ids): Builder
    {
        return $this->getActiveStudentsQuery()->whereIn('user_id', $ids)->with('user');
    }

    /**
     * Query builder student records (active or graduated) for a set of user IDs, with user relation.
     *
     * @param array $ids
     * @return Builder
     */
    public function getStudentsByUserIdsQuery(array $ids): Builder
    {
        return StudentRecord::whereIn('user_id', $ids)->with('user');
    }

    /**
     * Get all dormitories ordered by name.
     *
     * @return Collection
     */
    public function getAllDorms(): Collection
    {
        return Dorm::orderBy('name', 'asc')->get();
    }

    /**
     * Check if a student exists either as an active or graduated student.
     *
     * @param int $student_id  (user_id)
     * @return bool
     */
    public function exists(int $student_id): bool
    {
        return $this->getActiveStudentsQueryWithRelations(['user_id' => $student_id])->exists()
            || $this->getGraduatedStudentsQuery(['user_id' => $student_id])->exists();
    }

    /************* Promotions *************/

    /**
     * Create a new promotion record.
     *
     * @param array $data
     * @return Promotion
     */
    public function createPromotion(array $data): Promotion
    {
        return Promotion::create($data);
    }

    /**
     * Update promotion records matching conditions.
     *
     * @param array $where
     * @param array $data
     * @return int
     */
    public function updatePromotion(array $where, array $data): int
    {
        return Promotion::where($where)->update($data);
    }

    /**
     * Find a promotion by its primary key.
     *
     * @param int $id
     * @return Promotion|null
     */
    public function findPromotion(int $id): ?Promotion
    {
        return Promotion::find($id);
    }

    /**
     * Delete a promotion by its primary key.
     *
     * @param int $id
     * @return int  Number of deleted rows (0 or 1)
     */
    public function deletePromotion(int $id): int
    {
        return Promotion::destroy($id);
    }

    /**
     * Get all promotions for the current session → next session transition.
     *
     * @return Collection
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
     *
     * @param array $where
     * @return Collection
     */
    public function getPromotionsByConditions(array $where): Collection
    {
        return Promotion::where($where)->get();
    }
}
