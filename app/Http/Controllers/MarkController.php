<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Exports\MarksExport;
use App\Helpers\Mk;
use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mark\MarkSelector;
use App\Imports\MarksImport;
use App\Repositories\AssessmentRepo;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Rules\HasOrderedClassExamSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class MarkController extends Controller
{
    protected MyClassRepo $my_class;
    protected ExamRepo $exam;
    protected StudentRepo $student;
    protected string $year;
    protected MarkRepo $mark;
    protected AssessmentRepo $assessment;

    public function __construct(
        MyClassRepo $my_class,
        ExamRepo $exam,
        StudentRepo $student,
        MarkRepo $mark,
        AssessmentRepo $assessment
    ) {
        $this->permissionMap = config('permissions.names.tenant.marks');
        parent::__construct();
        $this->exam = $exam;
        $this->mark = $mark;
        $this->student = $student;
        $this->my_class = $my_class;
        $this->assessment = $assessment;
        $this->year = Mk::getCurrentSession();
    }

    /**
     * Display year selector view.
     */
    public function year_selector($student): View|RedirectResponse
    {
        return $this->verifyStudentExamYear($student);
    }

    /**
     * Handle year selection and redirect.
     */
    #[SkipPermission]
    public function year_selected(Request $req, $student): RedirectResponse
    {
        $year = $req->year;

        if (!$this->verifyStudentExamYear($student, $year)) {
            return $this->noStudentRecord();
        }

        $hashed_student = Mk::hash($student);

        if ($this->getExams($year)->isEmpty()) {
            return redirect()
                ->route('marks.year_selector', $hashed_student)
                ->with('flash_info', "No exams yet for the year $year.");
        }

        return redirect()->route('marks.show', [$hashed_student, $year]);
    }

    /**
     * Show student's marks for a year.
     */
    public function show($student, $year): View|RedirectResponse
    {
        $user = auth()->user();
        $is_staff = $user->hasAnyRole(Usr::getTenancyAwareStaffRoles());

        if (!$is_staff && $user->id != $student && !Mk::isStudentChildOfParent($student, $user->id)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        if (Mk::examIsLocked() && !$user->hasAnyRole($this->getAdminRoles())) {
            session()->put('marks_url', route('marks.show', [Mk::hash($student), $year]));

            if (!$this->checkPinVerified($student)) {
                return redirect()->route('pins.enter', Mk::hash($student));
            }
        }

        if (!$this->verifyStudentExamYear($student, $year)) {
            return $this->noStudentRecord();
        }

        $where = ['student_id' => $student, 'year' => $year];
        $exam_records = $this->exam->getExamRecords($where, ['section', 'division', 'grade']);

        if ($exam_records->isEmpty()) {
            return $this->noStudentRecord();
        }

        $my_class = $this->my_class->getClassesWithSections(['id' => $exam_records->value('my_class_id')])->first();

        return view('pages.marks.show.index', [
            'marks' => $this->exam->getMarksByConditions($where, ['grade']),
            'exam_records' => $exam_records,
            'exams' => $this->getExams($year),
            'sr' => $this->getStudentRecord($student),
            'my_class' => $my_class,
            'subjects' => $this->my_class->getAllSubjects(),
            'year' => $year,
            'student_id' => $student,
            'skills' => $this->exam->getSkillsByClassType(),
        ]);
    }

    /**
     * Show printable version of student's marks for a specific exam.
     */
    public function print_view($student, $exam_id, $year): View|RedirectResponse
    {
        $user = auth()->user();
        $is_staff = $user->hasAnyRole(Usr::getTenancyAwareStaffRoles());

        if (!$is_staff && $user->id != $student && !Mk::isStudentChildOfParent($student, $user->id)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        if (Mk::examIsLocked() && !$user->hasAnyRole($this->getAdminRoles())) {
            session()->put('marks_url', route('marks.show', [Mk::hash($student), $year]));

            if (!$this->checkPinVerified($student)) {
                return redirect()->route('pins.enter', Mk::hash($student));
            }
        }

        if (!$this->verifyStudentExamYear($student, $year)) {
            return $this->noStudentRecord();
        }

        $where = ['student_id' => $student, 'exam_id' => $exam_id, 'year' => $year];
        $exam_record = $this->exam->getExamRecords($where, ['section'])->first();

        if (!$exam_record) {
            return $this->noStudentRecord();
        }

        $my_class = $this->my_class->findClass($exam_record->my_class_id);
        $exam = $this->exam->find($exam_id);
        $class_type = $this->my_class->findClassTypeByClassId($my_class->id);

        return view('pages.marks.print.index', [
            'marks' => $this->exam->getMarksByConditions($where, ['grade']),
            'exr' => $exam_record,
            'my_class' => $my_class,
            'section' => $this->my_class->findSection($exam_record->section_id),
            'ex' => $exam,
            'tex' => "tex{$exam->term}",
            'sr' => $this->getStudentRecord($student),
            'class_type' => $class_type,
            'subjects' => $this->my_class->findSubjectByClass($my_class->id),
            'ct' => $class_type->code,
            'year' => $year,
            'student_id' => $student,
            'exam_id' => $exam_id,
            'skills' => $this->exam->getSkillsByClassType(),
        ]);
    }

    /**
     * Initialize marks for a class/exam/subject combination.
     */
    public function selector(MarkSelector $req): RedirectResponse
    {
        $data = $req->validated();
        $exam = $this->exam->find($data['exam_id'])->with('assessment')->first();
        $class_type = $this->my_class->findClassTypeByClassId($data['my_class_id']);

        if ($exam->class_type_id != $class_type->id) {
            return back()->with('flash_danger', __('msg.invalid_exam_and_class'));
        }

        $students = $this->getStudentsForSubject(
            $data['my_class_id'],
            $data['section_id'],
            $data['subject_id']
        );

        if ($students->isEmpty()) {
            return back()->with('pop_error', __('msg.rnf'));
        }

        $d2 = $req->only(['exam_id', 'my_class_id', 'section_id']);
        $d2['year'] = $this->year;

        foreach ($students as $student) {
            $data['student_id'] = $d2['student_id'] = $student->user_id;
            $data['section_id'] = $d2['section_id'] = $student->section_id;
            $data['year'] = $this->year;

            $mark = $this->exam->firstOrCreateMark($data);
            $this->exam->firstOrCreateExamRecord($d2);

            if ($exam->hasAssessment()) {
                $this->createAssessmentRecord($mark->id, $exam->assessment->id, $data);
            }
        }

        return redirect()->route('marks.manage', [
            $data['exam_id'],
            $data['my_class_id'],
            $data['subject_id'],
            $data['section_id'],
            true
        ]);
    }

    /**
     * Display marks management interface.
     */
    public function manage($exam_id = null, $class_id = null, $subject_id = null, $section_id = null, $selected = false): View|RedirectResponse
    {
        $data = [
            'selected' => $selected,
            'exams' => $this->exam->getExamsByConditions(['year' => $this->year]),
            'sections' => $this->my_class->getAllSections(),
            'subjects' => $this->my_class->getAllSubjects(),
        ];

        if ($selected) {
            $where = [
                'exam_id' => $exam_id,
                'my_class_id' => $class_id,
                'subject_id' => $subject_id,
                'year' => $this->year,
            ];

            if ($section_id !== null) {
                $where['section_id'] = $section_id;
            }

            $marks = $this->exam->getMarksByConditions($where, [
                'user',
                'user.student_record',
                'exam',
                'subject',
                'my_class',
                'section'
            ])->whereNotNull('user');

            if ($marks->isEmpty()) {
                return $this->noStudentRecord();
            }

            $first_mark = $marks->first();
            $data = [...$where, ...$data];
            $data['m'] = $first_mark;
            $data['marks'] = $marks;
            $data['exam'] = $this->exam->findExam($exam_id, false);
            $data['class_type'] = $this->my_class->findClassTypeByClassId($class_id);
            $data['created_at'] = Mk::formatDate($first_mark->created_at);
            $data['updated_at'] = Mk::formatDate($first_mark->updated_at);

            if (auth()->user()->hasRole(config('roles.default_roles.tenant.teacher'))) {
                $data['subjects'] = $this->my_class->findSubjectsByTeacherAndClass(auth()->id(), $class_id);
            }
        }

        return view('pages.marks.manage', $data);
    }

    /**
     * Update marks for a subject.
     */
    public function update(Request $req, $exam_id, $class_id, $subject_id, $section_id = null): JsonResponse
    {
        $exam = $this->exam->find($exam_id);

        if ($exam->is_locked) {
            return Mk::jsonUpdateDenied();
        }

        $class_type = $this->my_class->findClassTypeByClassId($class_id);
        [$marks, $where] = $this->fetchMarksForUpdate($exam_id, $class_id, $subject_id, $section_id);

        $form_data = $req->all();

        // Update subject marks
        $mark_updates = $this->buildSubjectMarkUpdates($marks, $form_data, $exam, $class_type->id, $class_id, $subject_id);
        $this->exam->massUpdateMark($mark_updates);

        // Update assessments
        if ($exam->hasAssessment()) {
            $assessment_updates = $this->buildAssessmentUpdates($marks, $form_data, $exam);
            $this->assessment->massUpdateRecords($assessment_updates, ['mark_id']);
        }

        // Recalculate student exam records
        $student_ids = $marks->pluck('student_id')->unique()->toArray();
        $exam_record_updates = $this->buildExamRecordUpdates(
            $student_ids,
            $exam,
            $class_id,
            $where,
            $class_type,
            $section_id ?: fn($st_id) => $marks->firstWhere('student_id', $st_id)->section_id ?? null
        );
        $this->exam->massUpdateExamRecords($exam_record_updates, ['student_id', 'exam_id']);

        // Update class positions
        $position_updates = $this->buildClassPositionUpdates($exam, $class_id, $student_ids);
        $this->exam->massUpdateExamRecords($position_updates, ['student_id', 'exam_id', 'my_class_id']);

        return Mk::jsonUpdateOk();
    }

    /**
     * Display batch operations page.
     */
    public function batch(): View
    {
        return view('pages.marks.batch.index', [
            'exams' => $this->exam->getExamsByConditions(['year' => $this->year]),
            'selected' => false,
        ]);
    }

    /**
     * Generate Excel template for batch upload.
     */
    public function batch_template(Request $req)
    {
        set_time_limit(1800);

        $class = $this->my_class->findClass($req->my_class_id);
        $exam = $this->exam->find($req->exam_id);

        if ($exam->class_type_id != $class->class_type_id) {
            return back()->with('flash_error', __('msg.invalid_exam_and_class'));
        }

        if (!$this->my_class->whereSubject(['my_class_id' => $class->id])->exists()) {
            return back()->with('flash_error', __('msg.assign_subjects_to_class'));
        }

        $students = $this->student->getActiveStudentsQueryWithRelations([
            'my_class_id' => $req->my_class_id,
            'session' => $this->year
        ])->get();

        if ($students->isEmpty()) {
            return redirect()->back()->with('flash_danger', __('msg.srnf'));
        }

        $this->initializeBatchMarks($exam, $class, $students, (bool) $req->delete_old_section_record);

        $data = [
            'marks' => $this->exam->getMarksByConditions(
                ['exam_id' => $exam->id, 'my_class_id' => $class->id, 'year' => $this->year],
                ['user', 'my_class', 'section', 'exam']
            ),
            'my_class_id' => $req->my_class_id,
            'year' => $this->year,
            'subjects' => Mk::getSubjects($class->id),
        ];

        $delimiter = Mk::getDelimiter();
        $time = time();
        $tenant_title = Usr::getSetting('system_title');
        $filename = str_replace(' ', '_', "{$class->name}{$delimiter}{$exam->name}{$delimiter}{$exam->year}{$delimiter}{$tenant_title}{$delimiter}{$time}.xlsx");

        return Excel::download(
            new MarksExport($data),
            $filename,
            \Maatwebsite\Excel\Excel::XLSX,
            [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        );
    }

    /**
     * Handle uploaded marks Excel file.
     */
    public function batch_upload(Request $req)
    {
        $template = $req->file('template');
        $delimiter = Mk::getDelimiter();
        // Remove file extension and unix timestamp
        $name = Str::beforeLast(pathinfo($template->getClientOriginalPath(), PATHINFO_FILENAME), $delimiter);
        $req['template_file_name'] = $name;

        Validator::make($req->toArray(), [
            'template' => 'required|file|mimes:xlsx,xlx|max:4096',
            'template_file_name' => new HasOrderedClassExamSession,
        ], [], ['template' => 'marks template'])->validate();

        if (!$req->hasFile('template')) {
            return Mk::json(__('msg.file_required'), false);
        }

        $exploded_name = explode($delimiter, $name);
        $year = "$exploded_name[2]-$exploded_name[3]";

        $class = $this->my_class->getClassesByConditions(['name' => str_replace('_', ' ', $exploded_name[0])]);
        $class_id = $class->value('id');

        $exam = $this->exam->getExamsByConditions(['name' => str_replace('_', ' ', $exploded_name[1]), 'year' => $year]);
        $exam_id = $exam->value('id');

        if ($exam->isEmpty()) {
            return Mk::json(__('msg.ernf'), false);
        }

        if ($exam->value('class_type_id') != $class->value('class_type_id')) {
            return Mk::json(__('msg.invalid_exam_and_class'), false);
        }

        $data = [
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
            'year' => $year,
            'session' => $this->year,
            'st_mark' => $this->exam->getMarksByConditions(['exam_id' => $exam_id, 'my_class_id' => $class_id, 'year' => $year], ['user']),
        ];

        Excel::import(new MarksImport($data), request()->file('template'));

        return Mk::jsonStoreOk();
    }

    /**
     * Perform batch recalculation of marks.
     */
    public function batch_update(Request $req): JsonResponse
    {
        set_time_limit(1800);

        $exam = $this->exam->find($req->exam_id);
        $class_type = $this->my_class->findClassTypeByClassId($req->my_class_id);

        if ($exam->class_type_id != $class_type->id) {
            return Mk::json(__('msg.invalid_exam_and_class'), false);
        }

        if ($this->exam->getMarksByConditions($req->only(['exam_id', 'my_class_id']), [])->isEmpty()) {
            return Mk::json(__('msg.rnf'), false);
        }

        $class_subjects = $this->my_class->whereSubject(['my_class_id' => $req->my_class_id])->get();
        $class_sections = $this->my_class->getSectionsByConditions(['my_class_id' => $req->my_class_id]);

        $all_mark_updates = $all_assessment_updates = $all_exam_record_updates = [];
        $all_student_ids = [];

        foreach ($class_sections as $section) {
            $section_result = $this->processSectionBatch(
                $exam,
                $req->my_class_id,
                $section->id,
                $class_type,
                $class_subjects
            );

            $all_mark_updates = [...$all_mark_updates, ...$section_result['marks']];
            $all_assessment_updates = [...$all_assessment_updates, ...$section_result['assessments']];
            $all_exam_record_updates = [...$all_exam_record_updates, ...$section_result['exam_records']];
            $all_student_ids = [...$all_student_ids, ...$section_result['student_ids']];
        }

        $this->exam->massUpdateMark($all_mark_updates);
        $this->exam->massUpdateExamRecords($all_exam_record_updates, ['student_id', 'exam_id']);

        if ($exam->hasAssessment()) {
            $this->assessment->massUpdateRecords($all_assessment_updates, ['mark_id']);
        }

        $position_updates = $this->buildClassPositionUpdates($exam, $req->my_class_id, array_unique($all_student_ids));
        $this->exam->massUpdateExamRecords($position_updates, ['student_id', 'exam_id', 'my_class_id']);

        return Mk::jsonUpdateOk();
    }

    /**
     * Delete marks in batch.
     */
    public function batch_delete(Request $req): RedirectResponse
    {
        Validator::make($req->toArray(), [
            'exam_id' => 'required|integer',
            'my_class_id' => 'required|integer',
        ], [], ['exam_id' => 'exam', 'my_class_id' => 'class'])->validate();

        $class_type = $this->my_class->findClassTypeByClassId($req->my_class_id);
        $exam = $this->exam->find($req->exam_id);

        if ($exam->class_type_id != $class_type->id) {
            return back()->with('flash_error', __('msg.invalid_exam_and_class'));
        }

        $where = $this->buildDeleteWhere($req);
        $marks = $this->exam->getMarksByConditions($where, []);

        if ($marks->isEmpty()) {
            return back()->with('flash_info', __('msg.rnf'));
        }

        $affected = $this->exam->deleteMarksByConditions($where);

        return back()->with('pop_success', __('msg.delete_ok') . ' With ' . $affected . ' affected records.');
    }

    /**
     * Update teacher/principal comments.
     */
    public function comment_update(Request $req, $exam_id): JsonResponse
    {
        $exam = $this->exam->find($exam_id);
        if ($exam->is_locked) {
            return Mk::jsonUpdateDenied();
        }

        $fields = auth()->user()->hasAnyRole($this->getAdminRoles())
            ? $req->only(['t_comment', 'p_comment'])
            : $req->only(['t_comment']);

        $this->exam->updateExamRecords(['exam_id' => $exam_id], $fields);

        return Mk::jsonUpdateOk();
    }

    /**
     * Update skills assessment.
     */
    public function skills_update(Request $req, $skill, $exr_id): JsonResponse
    {
        $updates = [];
        if (\in_array($skill, ['AF', 'PS'])) {
            $updates[$skill] = implode(',', $req->{strtolower($skill)});
        }

        $this->exam->updateExamRecords(['id' => $exr_id], $updates);

        return Mk::jsonUpdateOk();
    }

    /**
     * Display mark sheets page.
     */
    public function sheets($class_id = null, $section_id = null): View|RedirectResponse
    {
        if (!$this->exam->hasAnyExam()) {
            return $this->noExamsRecord();
        }

        $data = ['selected' => false];

        if ($class_id && $section_id) {
            $data['sections'] = $this->my_class->getAllSections()->where('my_class_id', $class_id);
            $data['students'] = $this->student->getActiveStudentsQueryWithRelations([
                'my_class_id' => $class_id,
                'section_id' => $section_id
            ])->get()->whereNotNull('user')->sortBy('user.name');

            if ($data['students']->isEmpty()) {
                return redirect()->route('marks.sheets')->with('flash_danger', __('msg.srnf'));
            }

            $data['selected'] = true;
            $data['my_class_id'] = $class_id;
            $data['section_id'] = $section_id;
        }

        return view('pages.marks.sheets', $data);
    }

    /**
     * Handle sheets selection redirect.
     */
    public function sheets_select(Request $req): RedirectResponse
    {
        return redirect()->route('marks.sheets', [$req->my_class_id, $req->section_id]);
    }

    /**
     * Handle tabulation selection redirect.
     */
    public function tabulation_select(Request $req): RedirectResponse
    {
        return redirect()->route('marks.tabulation', [
            $req->exam_id,
            $req->my_class_id,
            $req->section_id,
            $req->year
        ]);
    }

    public function tabulation($exam_id = null, $class_id = null, $section_id = null, $year = null)
    {
        set_time_limit(300);
        $data = [
            'years' => $this->exam->getDistinctExamYears(),
            'selected' => false,
            'year' => $year,
        ];

        if ($class_id && $exam_id) {
            $tab_data = $this->getTabulationData($exam_id, $class_id, $section_id, $year);
            if (isset($tab_data['error'])) {
                return Mk::goWithDanger('marks.tabulation', $tab_data['error']);
            }

            $data = [
                ...$data,
                ...$tab_data,
                'selected' => true,
                'exams' => $this->exam->getExamsByConditions(['year' => $year], false),
                'sections' => $this->my_class->getAllSections(),
            ];
        }

        return view('pages.marks.tabulation.index', $data);
    }

    public function print_tabulation($exam_id, $class_id, $section_id, $year)
    {
        set_time_limit(300);
        $tab_data = $this->getTabulationData($exam_id, $class_id, $section_id, $year);
        if (isset($tab_data['error'])) {
            return Mk::goWithDanger('marks.tabulation', $tab_data['error']);
        }

        $data = [
            ...$tab_data,
            'sections' => $section_id === 'all' ? $this->my_class->getAllSections() : null,
            'grades' => $section_id === 'all' ? $this->exam->getGrades() : null
        ];

        return view('pages.marks.tabulation.print', $data);
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Get exams for a year, filtered by publication status for non-admins.
     */
    protected function getExams($year): Collection
    {
        $exams = $this->exam->getExamsByConditions(['year' => $year], false);

        return auth()->user()->hasAnyRole($this->getAdminRoles())
            ? $exams
            : $exams->where('is_published', true);
    }

    /**
     * Verify if a student has exam records for a given year.
     */
    protected function verifyStudentExamYear($student_id, $year = null): View|RedirectResponse|bool
    {
        $years = $this->exam->getDistinctMarkYears($student_id);
        $student_exists = $this->student->exists($student_id);

        if ($year === null) {
            return $student_exists && $years->isNotEmpty()
                ? view('pages.marks.select_year', [
                    'years' => $years,
                    'student_id' => Mk::hash($student_id)
                ])
                : $this->noStudentRecord();
        }

        return $student_exists && $years->contains('year', $year);
    }

    /**
     * Redirect when no student record found.
     */
    protected function noStudentRecord(): RedirectResponse
    {
        return redirect()->route('dashboard')->with('flash_danger', __('msg.srnf'));
    }

    /**
     * Redirect when no exam record found.
     */
    protected function noExamsRecord(): RedirectResponse
    {
        return redirect()->route('dashboard')->with('flash_danger', __('msg.ernf'));
    }

    /**
     * Check if PIN has been verified for a student.
     */
    protected function checkPinVerified($st_id): bool
    {
        return session()->has('pin_verified') && session()->get('pin_verified') == $st_id;
    }

    /**
     * Get admin role IDs.
     */
    protected function getAdminRoles(): array
    {
        return [
            config('roles.default_roles.tenant.superadmin'),
            config('roles.default_roles.tenant.admin'),
        ];
    }

    /**
     * Get student record (latest).
     */
    protected function getStudentRecord($student_id)
    {
        return $this->student->getActiveStudentsQueryWithRelations(['user_id' => $student_id])->latest()->first()
            ?? $this->student->getGraduatedStudentsQuery(['user_id' => $student_id])->latest()->first();
    }

    /**
     * Get students eligible for a subject.
     */
    protected function getStudentsForSubject($class_id, $section_id, $subject_id): Collection
    {
        $sub_recs = $this->my_class->getSubjectRecord(['subject_id' => $subject_id])
            ->where('subject.my_class_id', $class_id);

        if ($section_id === null && $sub_recs->whereNull('section_id')->whereNotNull('students_ids')->isNotEmpty()) {
            return $this->student->getActiveStudentsByUserIdsQuery($sub_recs->value('students_ids'))->get();
        }

        if (
            $section_id === null && $sub_recs->whereNull('section_id')->whereNull('students_ids')->isNotEmpty()
            || $section_id !== null && $sub_recs->whereNotNull('students_ids')->isNotEmpty()
        ) {
            return collect(); // Will trigger error upstream
        }

        return $this->student->getActiveStudentsQueryWithRelations([
            'my_class_id' => $class_id,
            'section_id' => $section_id
        ])->get();
    }

    /**
     * Create assessment record for terminal/annual exams.
     */
    protected function createAssessmentRecord($mark_id, $assessment_id, array $data): void
    {
        $data['mark_id'] = $mark_id;
        $data['assessment_id'] = $assessment_id;
        $this->assessment->createRecord($data);
    }

    /**
     * Fetch marks for update operation.
     */
    protected function fetchMarksForUpdate($exam_id, $class_id, $subject_id, $section_id = null): array
    {
        $base = [
            'exam_id' => $exam_id,
            'my_class_id' => $class_id,
            'subject_id' => $subject_id,
            'year' => $this->year,
        ];

        if ($section_id === null) {
            $students_ids = $this->my_class->getSubjectRecord([
                'subject_id' => $subject_id,
                'section_id' => null
            ])->value('students_ids');

            $marks = $this->exam->getMarksByStudentIds($students_ids)->where($base)->get();
            $where = [
                'my_class_id' => $class_id,
                'exam_id' => $exam_id,
                'year' => $this->year
            ];
        } else {
            $base['section_id'] = $section_id;
            $marks = $this->exam->getMarksByConditions($base, ['user'])->whereNotNull('user');
            $where = $base;
        }

        unset($where['subject_id']);
        return [$marks, $where];
    }

    /**
     * Initialize batch marks for all students in class.
     */
    protected function initializeBatchMarks($exam, $class, $students, $delete_old_section_record): void
    {
        $assessment_id = $this->assessment->getAssessmentByConditions(['exam_id' => $exam->id])->id ?? null;
        $sections = $this->my_class->getSectionsByConditions(['my_class_id' => $class->id]);

        foreach ($sections as $sec) {
            $subjects_with_recs = $this->my_class->getSubjectsWithRecordsInSection($sec->id, $class->id);

            foreach ($students->where('section_id', $sec->id) as $st_rec) {
                foreach ($subjects_with_recs as $sub) {
                    $students_ids = $this->my_class->whereSubjectRecord(['subject_id' => $sub->id])
                        ->whereNotNull('students_ids')
                        ->value('students_ids');

                    $should_create = $students_ids === null || \in_array((int) $st_rec->user_id, array_map('intval', $students_ids));

                    if ($should_create) {
                        $mk = $this->mark->firstOrCreateMark(
                            $st_rec->user_id,
                            $class->id,
                            $sec->id,
                            $exam->id,
                            $this->year,
                            $sub->id
                        );
                        $this->exam->firstOrCreateExamRecord(
                            [
                                'student_id' => $st_rec->user_id,
                                'my_class_id' => $class->id,
                                'section_id' => $sec->id,
                                'exam_id' => $exam->id,
                                'year' => $this->year,
                            ]
                        );
                        if ($exam->hasAssessment() && $assessment_id) {
                            $this->assessment->firstOrCreateAssessmentRecord(
                                $st_rec->user_id,
                                $class->id,
                                $sec->id,
                                $exam->id,
                                $this->year,
                                $sub->id,
                                $mk->id,
                                $assessment_id
                            );
                        }
                    }
                }

                if ($delete_old_section_record) {
                    $old_section_id = $this->exam->getExamRecords([
                        'student_id' => $st_rec->user_id,
                        'exam_id' => $exam->id,
                        'my_class_id' => $class->id
                    ])->value('section_id');

                    if ($old_section_id != $st_rec->section_id) {
                        $where = [
                            'student_id' => $st_rec->user_id,
                            'exam_id' => $exam->id,
                            'my_class_id' => $class->id,
                            'section_id' => $old_section_id,
                        ];
                        $this->exam->deleteExamRecords($where);
                        $this->exam->deleteMarksByConditions($where);
                    }
                }
            }
        }
    }

    /**
     * Build where clause for batch delete.
     */
    protected function buildDeleteWhere(Request $req): array
    {
        $where = [
            'exam_id' => $req->exam_id,
            'my_class_id' => $req->my_class_id,
            'year' => $this->year,
        ];

        if ($req->section_id !== null && $req->subject_id !== null) {
            $where['section_id'] = $req->section_id;
            $where['subject_id'] = $req->subject_id;
        } elseif ($req->section_id === null && $req->subject_id !== null) {
            $where['subject_id'] = $req->subject_id;
        } elseif ($req->section_id !== null && $req->subject_id === null) {
            $where['section_id'] = $req->section_id;
        }

        return $where;
    }

    // ----------------------------------------------------------------------
    // Batch Processing Helper Methods
    // ----------------------------------------------------------------------

    private function processSectionBatch($exam, $class_id, $section_id, $class_type, $class_subjects): array
    {
        $p = ['exam_id' => $exam->id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $this->year];
        $wh = ['my_class_id' => $class_id, 'section_id' => $section_id, 'exam_id' => $exam->id, 'year' => $this->year];

        $marks_p = $this->exam->getMarksByConditions($p, ['user'])->whereNotNull('user');

        $mark_updates = $assessment_updates = [];
        $student_ids = [];

        foreach ($class_subjects as $sub) {
            $marks = $marks_p->where('subject_id', $sub->id);

            foreach ($marks as $mk) {
                $student_ids[] = $mk->student_id;

                $total = $mk->exam_mark;
                $grade = is_numeric($total) ? $this->mark->getGradeByTotal($total, $class_type->id) : null;

                $mark_updates[] = [
                    'id' => $mk->id,
                    "tex{$exam->term}" => $total > 100 ? null : $total,
                    'exam_mark' => $total > 100 ? null : $total,
                    'grade_id' => $grade?->id ?? null,
                    'subject_position' => $this->mark->getSubjectPosition($mk->student_id, $exam, $class_id, $sub->id, $this->year),
                ];

                if ($exam->hasAssessment()) {
                    $evaluated_val = $this->assessment->calculateProportionalValue($mk->exam_mark, $exam->tdt_denominator);
                    $assessment_updates[] = [
                        'mark_id' => $mk->id,
                        "tex{$exam->term}" => null,
                        'evaluated_exam_mark' => ($evaluated_val == 0) ? null : $evaluated_val,
                    ];
                }
            }
        }

        $exam_record_updates = $this->buildExamRecordUpdates(
            array_unique($student_ids),
            $exam,
            $class_id,
            $wh,
            $class_type,
            $section_id
        );

        return [
            'marks' => $mark_updates,
            'assessments' => $assessment_updates,
            'exam_records' => $exam_record_updates,
            'student_ids' => $student_ids,
        ];
    }

    private function buildSubjectMarkUpdates($marks, $form_data, $exam, $class_type_id, $class_id, $subject_id): array
    {
        $updates = [];
        foreach ($marks as $mk) {
            $exam_mark = $form_data["exm_{$mk->id}"];
            $total = $exam_mark;
            $grade = $this->mark->getGradeByTotal($total, $class_type_id);

            $updates[] = [
                'id' => $mk->id,
                'exam_mark' => $total > 100 ? null : $exam_mark,
                "tex{$exam->term}" => $total > 100 ? null : $total,
                'grade_id' => $grade?->id,
                'subject_position' => $this->mark->getSubjectPosition($mk->student_id, $exam, $class_id, $subject_id, $this->year),
            ];
        }
        return $updates;
    }

    private function buildAssessmentUpdates($marks, $form_data, $exam): array
    {
        $updates = [];
        foreach ($marks as $mk) {
            $evaluated_val = $this->assessment->calculateProportionalValue(
                $form_data["exm_{$mk->id}"],
                $exam->tdt_denominator
            );
            $updates[] = [
                'mark_id' => $mk->id,
                "tex{$exam->term}" => null,
                'evaluated_exam_mark' => ($evaluated_val == 0) ? null : $evaluated_val,
            ];
        }
        return $updates;
    }

    private function buildExamRecordUpdates(array $student_ids, $exam, $class_id, $whereConditions, $class_type, $section_identifier): array
    {
        $sub_ids = $this->mark->getDistinctSubjectIds($whereConditions);
        $subjects = $this->my_class->getSubjectsByIds($sub_ids, []);
        $subjects_count = $subjects->count();
        $marks = $this->exam->getMarksByConditions($whereConditions, ['subject', 'grade']);

        $class_type_id = $class_type->id;
        $minimum_subjects_required = (int) $this->my_class->findClassType($class_type_id)->minimum_subjects_required;

        $updates = [];
        foreach ($student_ids as $st_id) {
            $section_id = is_callable($section_identifier) ? $section_identifier($st_id) : $section_identifier;

            if ($subjects_count < $minimum_subjects_required && $minimum_subjects_required !== 0) {
                $average = $points_sum = $total_credits = 0;
            } else {
                $average = $this->mark->getLimitedTermAverage(
                    $exam,
                    $st_id,
                    $class_id,
                    $section_id,
                    $this->year,
                    $minimum_subjects_required
                );
                $grade = $this->mark->getGradeByTotal($average, $class_type_id);

                $credits_point = $credits = [];
                foreach ($subjects as $sub) {
                    $mark = $marks->where('student_id', $st_id)
                        ->where('subject_id', $sub->id)
                        ->where('subject.is_core', true)
                        ->first();
                    if ($mark?->grade) {
                        $credits_point[] = $mark->grade->point * $mark->subject->credits;
                    }
                    $credits[] = $sub->credits;
                }

                $total_credits = $this->mark->sumSmallestValues($credits, $minimum_subjects_required);
                $total_credits_point = $this->mark->sumSmallestValues($credits_point, $minimum_subjects_required);

                $points_sum = $this->mark->sumSmallestValues(
                    $marks->where('student_id', $st_id)->pluck('grade')->pluck('point')->toArray(),
                    $minimum_subjects_required
                );
            }

            $updates[] = [
                'student_id' => $st_id,
                'exam_id' => $exam->id,
                'total' => $this->mark->getTermTotalForExam($exam, $st_id, $class_id, $this->year),
                'average' => $average,
                'grade_id' => $grade->id ?? null,
                'class_average' => $this->mark->getClassAverage($exam, $class_id, $this->year) ?? null,
                'section_position' => $this->exam->getStudentPositionByColumn($st_id, $exam->id, $class_id, $this->year, $exam->exam_student_position_by_value, $exam->exam_student_position_sort_order, $section_id),
                'points' => $points_sum,
                'gpa' => ($total_credits > 0) ? round($total_credits_point / $total_credits, 2) : 0,
                'division_id' => Mk::getDivisionByPoints($points_sum, $class_type_id)->id ?? null,
            ];
        }

        return $updates;
    }

    /**
     * Fetch and prepare all data needed for tabulation (display and print).
     */
    protected function getTabulationData($exam_id, $class_id, $section_id, $year): array
    {
        $my_class = $this->my_class->findClass($class_id);
        $exam = $this->exam->find($exam_id);

        $where = [
            'my_class_id' => $class_id,
            'exam_id' => $exam_id,
            'year' => $year,
        ];

        if ($section_id !== 'all') {
            $where['section_id'] = $section_id;
        }

        $sub_ids = $this->mark->getDistinctSubjectIds($where);
        $st_ids = $this->mark->getDistinctStudentIds($where);

        if (\count($sub_ids) < 1 || \count($st_ids) < 1) {
            return ['error' => __('msg.srnf')];
        }

        $exam_records = $this->exam->getExamRecords($where, ['grade', 'division'])->keyBy('student_id');
        if ($exam_records->isEmpty()) {
            return ['error' => __('msg.ernf')];
        }

        $subjects = $this->my_class->getSubjectsByIds($sub_ids);
        $marks_collection = $this->exam->getMarksByConditions($where, ['grade', 'subject'])->keyBy(
            fn($item) => "{$item->student_id}_{$item->subject_id}"
        );

        $students = $this->student->getStudentsByUserIdsQuery($st_ids->toArray())
            ->get()
            ->whereNotNull('user')
            ->sortBy('user.name');

        $tex = "tex{$exam->term}";
        $students_data = [];

        foreach ($students as $s) {
            $student_id = $s->user_id;
            $record = $exam_records[$student_id] ?? null;
            $total = $record->total ?? 0;

            $row = [
                'user' => $s->user,
                'gender' => $s->user->gender,
                'total' => $total,
                'average' => $record->average ?? null,
                'grade' => $record->grade->name ?? null,
                'points' => $record->points ?? null,
                'division' => $record->division->name ?? null,
                'gpa' => $record->gpa ?? null,
                'pos' => $section_id !== 'all' ? ($record->section_position ?? null) : ($record->class_position ?? null),
                'sub_marks' => [],
            ];

            foreach ($subjects as $sub) {
                $key = "{$student_id}_{$sub->id}";
                $mark = $marks_collection[$key] ?? null;
                $row['sub_marks'][$sub->id] = [
                    'mark' => $mark->$tex ?? null,
                    'grade' => $mark->grade->name ?? null,
                ];
            }

            $students_data[] = $row;
        }

        return [
            'my_class' => $my_class,
            'section' => $section_id !== 'all' ? $this->my_class->findSection($section_id) : null,
            'ex' => $exam,
            'tex' => $tex,
            'subjects' => $subjects,
            'students_data' => $students_data,
            'class_type_id' => $my_class->class_type_id,
            'year' => $year,
            'exam_id' => $exam_id,
            'section_id' => $section_id,
        ];
    }

    private function buildClassPositionUpdates($exam, $class_id, array $student_ids): array
    {
        $updates = [];
        foreach ($student_ids as $st_id) {
            $updates[] = [
                'student_id' => $st_id,
                'exam_id' => $exam->id,
                'my_class_id' => $class_id,
                'class_position' => $this->exam->getStudentPositionByColumn($st_id, $exam->id, $class_id, $this->year, $exam->exam_student_position_by_value, $exam->exam_student_position_sort_order),
            ];
        }
        return $updates;
    }
}