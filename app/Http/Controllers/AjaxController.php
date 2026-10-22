<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\ExamRepo;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;

class AjaxController extends Controller
{
    public function __construct(
        protected LocationRepo $loc,
        protected MyClassRepo $my_class,
        protected StudentRepo $student,
        protected ExamRepo $exam
    ) {}

    /**
     * Get columns of a given database table.
     */
    public function get_table_columns(string $table_name): array
    {
        return Qs::getTableCols($table_name);
    }

    /**
     * Get states by nationality ID.
     */
    public function get_state(int $nationality_id): array
    {
        $lgas = $this->loc->getStatesByCountryId($nationality_id);

        return $lgas->map(fn ($q) => ['id' => $q->id, 'name' => $q->name])->values()->all();
    }

    /**
     * Get LGAs by state ID.
     */
    public function get_lga(int $state_id): array
    {
        $lgas = $this->loc->getStateById($state_id);

        return $lgas->map(fn ($q) => ['id' => $q->id, 'name' => $q->name])->values()->all();
    }

    /**
     * Get sections of a class.
     */
    public function get_class_sections(int $class_id): array
    {
        $sections = $this->my_class->getClassSections($class_id);

        return $sections->map(fn ($q) => ['id' => $q->id, 'name' => $q->name])->values()->all();
    }

    /**
     * Get students of a class.
     */
    public function get_class_students(int $class_id): array
    {
        $students = $this->student->getActiveStudentsQueryWithRelations(['my_class_id' => $class_id, 'session' => Qs::getCurrentSession()])->get()->whereNotNull('user');

        return $students->map(fn ($q) => ['id' => $q->user->id, 'name' => $q->user->name])->values()->all();
    }

    /**
     * Get class sections taught by the authenticated teacher.
     */
    public function get_teacher_class_sections(int $class_id): array
    {
        $sections = $this->my_class->getTeacherClassSections($class_id, auth()->id());

        return $sections->map(fn ($q) => ['id' => $q->id, 'name' => $q->name])->values()->all();
    }

    /**
     * Get exams for a given year.
     */
    public function get_year_exams(string $year): array
    {
        $exams = $this->exam->getExamsByConditions(['year' => $year], false);

        return $exams->map(fn ($q) => ['id' => $q->id, 'name' => $q->name])->values()->all();
    }

    /**
     * Get teacher assigned to a subject and section.
     */
    public function get_subject_section_teacher(int $subject_id, int $section_id): array
    {
        $record = $this->my_class->whereSubjectRecord(['subject_id' => $subject_id, 'section_id' => $section_id])->with('teacher')->first();

        if (! $record || ! $record->teacher) {
            return ['id' => null, 'name' => null];
        }

        return [
            'id' => Qs::hash($record->teacher_id),
            'name' => $record->teacher->name,
        ];
    }

    /**
     * Get subjects for a class, filtered by teacher role if applicable.
     */
    public function get_class_subjects(int $class_id): array
    {
        $subjects = auth()->user()->hasRole(config('roles.default_roles.tenant.teacher'))
            ? $this->my_class->findSubjectsByTeacherAndClass(auth()->id(), $class_id)
            : $this->my_class->findSubjectByClass($class_id);

        return $subjects->map(fn ($q) => [
            'id' => $q->id ?? $q->subject->id ?? null,
            'name' => $q->name ?? $q->subject->name ?? null,
        ])->values()->all();
    }
}
