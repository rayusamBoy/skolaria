<?php

namespace App\Repositories;

use App\Models\ClassType;
use App\Models\MyClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class MyClassRepo
{
    /*********** My class ***************/

    /**
     * Get classes matching the given conditions.
     *
     * @param array $where
     * @return EloquentCollection
     */
    public function getClassesByConditions(array $where): EloquentCollection
    {
        return MyClass::where($where)->get();
    }

    /**
     * Get a query builder for classes matching conditions, including sections.
     *
     * @param array $where
     * @return Builder
     */
    public function getClassesWithSections(array $where): Builder
    {
        return MyClass::where($where)->with('section');
    }

    /**
     * Get only soft‑deleted classes.
     *
     * @return EloquentCollection
     */
    public function getTrashedClasses(): EloquentCollection
    {
        return MyClass::onlyTrashed()->get();
    }

    /**
     * Restore a soft‑deleted class by ID.
     *
     * @param int $id
     * @return int  Number of restored rows
     */
    public function restoreClass(int $id): int
    {
        return MyClass::where('id', $id)->restore();
    }

    /**
     * Permanently delete a soft‑deleted class.
     *
     * @param int $id
     * @return int  Number of deleted rows
     */
    public function forceDeleteClass(int $id): int
    {
        return MyClass::where('id', $id)->forceDelete();
    }

    /**
     * Find a class by its primary key.
     *
     * @param int $id
     * @return MyClass|null
     */
    public function findClass(int $id): ?MyClass
    {
        return MyClass::find($id);
    }

    /**
     * Create a new class.
     *
     * @param array $data
     * @return MyClass
     */
    public function createClass(array $data): MyClass
    {
        return MyClass::create($data);
    }

    /**
     * Update a class by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateClass(int $id, array $data): int
    {
        return MyClass::where('id', $id)->update($data);
    }

    /**
     * Delete a class by ID (soft delete).
     *
     * @param int $id
     * @return int
     */
    public function deleteClass(int $id): int
    {
        return MyClass::destroy($id);
    }

    /**
     * Check if a class has student records.
     *
     * @param int $id
     * @return bool
     */
    public function hasAnyClassStudents(int $id): bool
    {
        return MyClass::where('id', $id)->whereHas('student_record')->exists();
    }

    /*********** Class type ***************/

    /**
     * Get all class types ordered by name ascending.
     *
     * @return EloquentCollection
     */
    public function getAllClassTypes(): EloquentCollection
    {
        return ClassType::orderBy('name', 'asc')->get();
    }

    /**
     * Find a class type by its primary key.
     *
     * @param int $class_type_id
     * @return ClassType|null
     */
    public function findClassType(int $class_type_id): ?ClassType
    {
        return ClassType::find($class_type_id);
    }

    /**
     * Get the class type associated with a given class ID.
     *
     * @param int $class_id
     * @return ClassType|null
     */
    public function findClassTypeByClassId(int $class_id): ?ClassType
    {
        $class = $this->findClass($class_id);
        return $class ? $this->findClassType($class->class_type_id) : null;
    }

    /**
     * Create a new class type.
     *
     * @param array $data
     * @return ClassType
     */
    public function createClassType(array $data): ClassType
    {
        return ClassType::create($data);
    }

    /************* Section *******************/

    /**
     * Create a new section.
     *
     * @param array $data
     * @return Section
     */
    public function createSection(array $data): Section
    {
        return Section::create($data);
    }

    /**
     * Get sections matching the given conditions.
     *
     * @param array $data
     * @return EloquentCollection
     */
    public function getSectionsByConditions(array $data): EloquentCollection
    {
        return Section::where($data)->get();
    }

    /**
     * Find a section by its primary key.
     *
     * @param int $id
     * @return Section|null
     */
    public function findSection(int $id): ?Section
    {
        return Section::find($id);
    }

    /**
     * Update a section by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateSection(int $id, array $data): int
    {
        return Section::where('id', $id)->update($data);
    }

    /**
     * Delete a section by ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteSection(int $id): int
    {
        return Section::destroy($id);
    }

    /**
     * Get all sections with their class and teacher, ordered by name ascending.
     *
     * @return EloquentCollection
     */
    public function getAllSections(): EloquentCollection
    {
        return Section::orderBy('name', 'asc')->with(['my_class', 'teacher'])->get();
    }

    /**
     * Get all sections belonging to a specific class, ordered by name ascending.
     *
     * @param int $class_id
     * @return EloquentCollection
     */
    public function getClassSections(int $class_id): EloquentCollection
    {
        return Section::where('my_class_id', $class_id)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get sections belonging to a class and assigned to a specific teacher.
     *
     * @param int $class_id
     * @param int $teacher_id
     * @return EloquentCollection
     */
    public function getTeacherClassSections(int $class_id, int $teacher_id): EloquentCollection
    {
        return Section::where('my_class_id', $class_id)
            ->where('teacher_id', $teacher_id)
            ->orderBy('name', 'asc')
            ->get();
    }

    /************* Subject *******************/

    /**
     * Create a new subject.
     *
     * @param array $data
     * @return Subject
     */
    public function createSubject(array $data): Subject
    {
        return Subject::create($data);
    }

    /**
     * Find a subject by its primary key.
     *
     * @param int $id
     * @return Subject|null
     */
    public function findSubject(int $id): ?Subject
    {
        return Subject::find($id);
    }

    /**
     * Get subjects for a class, ordered by a given column.
     *
     * @param int    $class_id
     * @param string $order_by
     * @return EloquentCollection
     */
    public function findSubjectByClass(int $class_id, string $order_by = 'name'): EloquentCollection
    {
        return $this->whereSubject(['my_class_id' => $class_id])
            ->orderBy($order_by)
            ->get();
    }

    /**
     * Get all subjects taught by a specific teacher.
     *
     * @param int $user_id
     * @return EloquentCollection
     */
    public function findSubjectsByTeacher(int $user_id): EloquentCollection
    {
        $subject_ids = $this->whereSubjectRecord(['teacher_id' => $user_id])
            ->pluck('subject_id');
        return Subject::whereIn('id', $subject_ids)->get();
    }

    /**
     * Get a query builder for subjects matching conditions.
     *
     * @param array $where
     * @return Builder
     */
    public function whereSubject(array $where): Builder
    {
        return Subject::where($where);
    }

    /**
     * Get subjects that have a record in a given section, or are global (NULL section).
     *
     * @param int $section_id
     * @param int $class_id
     * @return EloquentCollection
     */
    public function getSubjectsWithRecordsInSection(int $section_id, int $class_id): EloquentCollection
    {
        return Subject::with('record')
            ->whereHas('record', fn($q) => $q->where('section_id', $section_id)
                ->orWhereNull('section_id'))
            ->where('my_class_id', $class_id)
            ->get();
    }

    /**
     * Get subjects by a list of IDs, with optional relations.
     *
     * @param array|Collection $ids
     * @param array            $with
     * @return EloquentCollection
     */
    public function getSubjectsByIds(array|Collection $ids, array $with = ['record']): EloquentCollection
    {
        return Subject::with($with)
            ->whereIn('id', $ids instanceof Collection ? $ids->toArray() : $ids)
            ->orderBy('name')
            ->get();
    }

    /**
     * Update a subject by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateSubject(int $id, array $data): int
    {
        return Subject::where('id', $id)->update($data);
    }

    /**
     * Delete a subject by ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteSubject(int $id): int
    {
        return Subject::destroy($id);
    }

    /**
     * Get all subjects with optional relations, ordered by name ascending.
     *
     * @param array $with
     * @return EloquentCollection
     */
    public function getAllSubjects(array $with = ['my_class', 'record']): EloquentCollection
    {
        return Subject::orderBy('name', 'asc')->with($with)->get();
    }

    /**
     * Get distinct subject names with their class.
     *
     * @return EloquentCollection
     */
    public function getUniqueSubjectsNames(): EloquentCollection
    {
        return Subject::select('name')
            ->orderBy('name', 'asc')
            ->distinct()
            ->with('my_class')
            ->get();
    }

    /**
     * Get the array of column names for a subject (for imports/exports).
     *
     * @param array $remove
     * @return array
     */
    public function getSubjectData(array $remove = []): array
    {
        $data = ['name', 'slug', 'my_class_id', 'is_core', 'credits'];
        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    /**
     * Get the array of column names for a subject record.
     *
     * @param array $remove
     * @return array
     */
    public function getSubjectRecordData(array $remove = []): array
    {
        $data = ['subject_id', 'section_id', 'teacher_id', 'students_ids'];
        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    /************* Subject records *******************/

    /**
     * Find a subject record by ID.
     *
     * @param int $id
     * @return SubjectRecord|null
     */
    public function findSubjectRecord(int $id): ?SubjectRecord
    {
        return SubjectRecord::with('subject')->find($id);
    }

    /**
     * Create a new subject record.
     *
     * @param array $data
     * @return SubjectRecord
     */
    public function createSubjectRecord(array $data): SubjectRecord
    {
        return SubjectRecord::create($data);
    }

    /**
     * Get a query builder for subject records matching conditions.
     *
     * @param array $where
     * @return Builder
     */
    public function whereSubjectRecord(array $where): Builder
    {
        return SubjectRecord::where($where);
    }

    /**
     * Get subject records matching conditions with optional relations.
     *
     * @param array $where
     * @param array $with
     * @return EloquentCollection
     */
    public function getSubjectRecord(array $where, array $with = ['subject']): EloquentCollection
    {
        return $this->whereSubjectRecord($where)->with($with)->get();
    }

    /**
     * Get subject records for a teacher, ordered by a column.
     *
     * @param int    $teacher_id
     * @param string $order_by
     * @return EloquentCollection
     */
    public function findSubjectRecordsByTeacher(int $teacher_id, string $order_by = 'id'): EloquentCollection
    {
        return SubjectRecord::where('teacher_id', $teacher_id)
            ->with('subject')
            ->orderBy($order_by)
            ->get();
    }

    /**
     * Update subject records matching conditions.
     *
     * @param array $where
     * @param array $data
     * @return int
     */
    public function updateSubjectRecords(array $where, array $data): int
    {
        return SubjectRecord::where($where)->update($data);
    }

    /**
     * Delete subject records matching conditions.
     *
     * @param array $where
     * @return int
     */
    public function deleteSubjectRecords(array $where): int
    {
        return SubjectRecord::where($where)->delete();
    }

    /**
     * Get distinct subject records for a teacher with additional relations.
     *
     * @param int   $teacher_id
     * @param array $relations
     * @return EloquentCollection
     */
    public function getSubjectRecordsForTeacher(int $teacher_id, array $relations): EloquentCollection
    {
        return SubjectRecord::where('teacher_id', $teacher_id)
            ->with($relations)
            ->distinct()
            ->get();
    }

    /**
     * Get subjects taught by a teacher in a specific class, sorted by subject name.
     *
     * @param int    $teacher_id
     * @param int    $class_id
     * @param string $sort_by
     * @return EloquentCollection
     */
    public function findSubjectsByTeacherAndClass(int $teacher_id, int $class_id, string $sort_by = 'subject.name'): EloquentCollection
    {
        return $this->getSubjectRecordsForTeacher($teacher_id, ['subject'])
            ->where('subject.my_class_id', $class_id)
            ->sortBy($sort_by);
    }
}
