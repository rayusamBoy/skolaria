<?php

namespace App\Http\Controllers;

use App\Helpers\Mk;
use App\Http\Controllers\Controller;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Validators\ExamIdValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StatisticsController extends Controller
{
    protected UserRepo $user;
    protected MyClassRepo $class;
    protected ExamRepo $exam;
    protected StudentRepo $student;
    protected MarkRepo $mark;
    protected string $year;

    public function __construct(
        UserRepo $user,
        MyClassRepo $class,
        ExamRepo $exam,
        StudentRepo $student,
        MarkRepo $mark
    ) {
        $this->permissionMap = config('permissions.names.tenant.statistics');
        parent::__construct();
        $this->user = $user;
        $this->class = $class;
        $this->exam = $exam;
        $this->student = $student;
        $this->mark = $mark;
        $this->year = Mk::getSetting('current_session');
    }

    /**
     * Display statistics dashboard based on user role.
     */
    public function index($exam_id = ''): View|RedirectResponse
    {
        $exams = $this->exam->getLatestPublished();
        if ($exams->isEmpty()) {
            return redirect()->back()->with('flash_error', __('msg.pernf'));
        }

        $exam_id = Mk::decodeHash($exam_id);
        ExamIdValidator::validate($exam_id);

        $last_exam = $exam_id
            ? $this->exam->findExam($exam_id)
            : $exams->first();


        $user = auth()->user();
        $user_is_parent = $user->hasRole(config('roles.default_roles.tenant.parent'));
        $user_is_student = $user->hasRole(config('roles.default_roles.tenant.student'));
        $user_roles_count = $user->roles->count();
        $user_is_staff = ($user_is_parent && $user_roles_count > 1) || (!$user_is_student && !$user_is_parent && $user_roles_count === 1);

        $data = [
            'last_exam' => $last_exam,
            'prev_exam_id' => $exams->where('id', '<', $last_exam->id)->first()->id ?? null,
            'next_exam_id' => $exams->where('id', '>', $last_exam->id)->first()->id ?? null,
            'year' => $this->year,
            'user_is_parent' => $user_is_parent,
            'user_is_student' => $user_is_student,
            'user_is_staff' => $user_is_staff,
        ];

        if ($user_is_parent) {
            $data = [...$data, ...$this->getParentStatistics($last_exam)];
        }
        if ($user_is_student) {
            $data = [...$data, ...$this->getStudentStatistics($last_exam)];
        }
        if ($user_is_staff) {
            $data = [...$data, ...$this->getStaffStatistics($last_exam, $exams)];
        }

        return view('pages.statistics.index', $data);
    }

    /**
     * Get statistics for parent users.
     */
    protected function getParentStatistics($last_exam): array
    {
        $student_ids = $this->student->getActiveStudentsQuery()
            ->where('my_parent_id', auth()->id())
            ->pluck('user_id');

        $exams_records = $this->exam->getExamRecordsByStudents($student_ids->toArray())
            ->where('year', $this->year)
            ->get()->where('exam.is_published', true);

        $marks = $this->mark->getMarksForStudents($student_ids->toArray())
            ->where('year', $this->year)->orderByDesc('id')
            ->get();

        $data = ['exams_recs' => $exams_records];

        if ($marks->isNotEmpty()) {
            $data['last_exm_marks'] = $marks->where('exam_id', $last_exam->id);
        }

        return $data;
    }

    /**
     * Get statistics for student users.
     */
    protected function getStudentStatistics($last_exam): array
    {
        return [
            'exams_recs' => $this->exam->getExamRecordsByStudent(auth()->id())
                ->where('year', $this->year)
                ->get()->where('exam.is_published', true),
            'last_exm_marks' => $this->mark->getMarksByStudentId(auth()->id())
                ->where('exam_id', $last_exam->id)
                ->get(),
        ];
    }

    /**
     * Get statistics for staff/admin users (charts and averages).
     */
    protected function getStaffStatistics($last_exam, $exams): array
    {
        $exams_records = $this->exam->getExamRecordsByExam($last_exam->id)
            ->get()
            ->unique('my_class.id')
            ->sortBy('my_class.id');

        $data = [
            'classes_names' => $exams_records->pluck('my_class.name'),
            'averages' => $exams_records->pluck('class_average'),
            'exams_names' => null,
            'exams_gpas' => null,
        ];

        $this_year_exams = $this->exam->getPublished(['year' => $this->year]);

        if ($this_year_exams->isNotEmpty()) {
            $exam_names = [];
            $exam_gpas = [];
            $class_types = $this->class->getAllClassTypes();

            foreach ($class_types as $type) {
                foreach ($this_year_exams as $exam) {
                    $exam_names[] = $exam->name;
                    $gpa = Mk::getGPA($exam->id, null, $type->id, null);
                    if ($gpa !== null) {
                        $exam_gpas[] = $gpa;
                    }
                }
            }

            $data['exams_names'] = array_unique($exam_names);
            $data['exams_gpas'] = $exam_gpas;
        }

        return $data;
    }
}
