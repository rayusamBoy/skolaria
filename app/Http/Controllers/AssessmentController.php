<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Helpers\Mk;
use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Http\Requests\Assessment\AssessmentSelector;
use App\Repositories\AssessmentRepo;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    protected ExamRepo $exam;
    protected AssessmentRepo $assessment;
    protected MyClassRepo $my_class;
    protected StudentRepo $student;
    protected MarkRepo $mark;
    protected string $year;

    protected const int CW_COUNT = 10;
    protected const int HW_COUNT = 5;
    protected const int TT_COUNT = 3;

    public function __construct(
        ExamRepo $exam,
        AssessmentRepo $assessment,
        MyClassRepo $my_class,
        StudentRepo $student,
        MarkRepo $mark
    ) {
        $this->permissionMap = config('permissions.names.tenant.assessments');
        parent::__construct();
        $this->exam = $exam;
        $this->assessment = $assessment;
        $this->my_class = $my_class;
        $this->student = $student;
        $this->mark = $mark;
        $this->year = Mk::getSetting('current_session');
    }

    /**
     * Show a list of assessments.
     */
    public function list(): View
    {
        $data['assessments'] = $this->assessment->getAssessmentsWithValidExam([], ['exam', 'exam.category']);
        $data['exams_cat'] = $this->exam->getCategories();

        return view('pages.assessments.list', $data);
    }

    /**
     * Show the main assessments page.
     */
    public function index(): View
    {
        $data['assessments'] = $this->assessment->getAssessmentsWithValidExam([]);
        $data['subjects'] = $this->my_class->getAllSubjects();
        $data['selected'] = false;

        return view('pages.assessments.manage', $data);
    }

    /**
     * Handle assessment selector and redirect to manage page.
     */
    public function selector(AssessmentSelector $req): RedirectResponse
    {
        $classSection = $req->only(['my_class_id', 'section_id']);
        $classSection['session'] = $this->year;

        $sub_record = $this->my_class->getSubjectRecord(['subject_id' => $req->subject_id])
            ->where('subject.my_class_id', $req->my_class_id)
            ->first();

        if ($sub_record === null) {
            return back()->with('pop_error', __('msg.rnf'));
        }

        $students = ($req->section_id === null && $sub_record->students_ids !== null)
            ? $this->student->getActiveStudentsByUserIds($sub_record->students_ids)->get()
            : $this->student->getActiveStudentsQueryWithRelations($classSection)->get();

        if ($students->isEmpty()) {
            return back()->with('pop_error', __('msg.rnf'));
        }

        return redirect()->route('assessments.manage', [
            $req->assessment_id,
            $req->my_class_id,
            $req->subject_id,
            $req->section_id
        ]);
    }

    /**
     * Manage assessments for a given exam/class/subject.
     */
    public function manage($assessment_id, $class_id, $subject_id, $section_id = null): View|RedirectResponse
    {
        $where = $section_id === null
            ? ['assessment_id' => $assessment_id, 'my_class_id' => $class_id, 'subject_id' => $subject_id, 'year' => $this->year]
            : ['assessment_id' => $assessment_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $this->year];

        $records = $this->assessment->getAssessmentRecordsWithRelations($where, ['exam', 'user', 'subject', 'my_class', 'section']);

        if ($records->isEmpty()) {
            return back()->with('flash_danger', __('msg.srnf'));
        }

        $first = $records->first();

        $data = [
            'assessment_id' => $assessment_id,
            'asmnt_records' => $records,
            'm' => $first,
            'assessments' => $this->assessment->getAssessmentsWithValidExam([])->where('exam.year', $this->year),
            'sections' => $this->my_class->getAllSections(),
            'subjects' => auth()->user()->hasRole(config('roles.default_roles.tenant.teacher'))
                ? $this->my_class->findSubjectsByTeacherAndClass(auth()->id(), $class_id)->pluck('subject')
                : $this->my_class->whereSubject(['my_class_id' => $class_id])->get(),
            'selected' => true,
            'class_type' => $this->my_class->findClassTypeByClassId($class_id),
            'created_at' => Mk::formatDate($this->assessment->getAssessmentRecordValue($where, 'created_at')),
            'updated_at' => Mk::formatDate($this->assessment->getAssessmentRecordValue($where, 'updated_at')),
            'section_id' => $section_id,
        ];

        return view('pages.assessments.manage', $data);
    }

    /**
     * Show assessment sheets for a class/section.
     */
    public function sheets($class_id = null, $section_id = null): View|RedirectResponse
    {
        if (!$this->assessment->hasAnyAssessmentWithExam()) {
            return $this->noAssessmentRecord();
        }

        $data['selected'] = false;

        if ($class_id && $section_id) {
            $data['sections'] = $this->my_class->getAllSections()->where('my_class_id', $class_id);
            $students = $this->student->getActiveStudentsQueryWithRelations(['my_class_id' => $class_id, 'section_id' => $section_id])
                ->get()
                ->sortBy('user.name');

            if ($students->isEmpty()) {
                return redirect()->route('assessments.sheets')->with('flash_danger', __('msg.srnf'));
            }

            $data['students'] = $students;
            $data['selected'] = true;
            $data['my_class_id'] = $class_id;
            $data['section_id'] = $section_id;
        }

        return view('pages.assessments.sheets', $data);
    }

    /**
     * Show progressive assessment report.
     */
    public function progressive($assessment_id = null, $class_id = null, $section_id = null): View|RedirectResponse
    {
        set_time_limit(300);

        $assessments = $this->assessment->getAssessmentsWithValidExam([])->where('exam.year', $this->year);
        $data = [
            'assessments' => $assessments,
            'teacher_class_ids' => $this->my_class->getSectionsByConditions(["teacher_id" => auth()->id()])->pluck("my_class_id"),
            'selected' => false,
            'user_is_class_section_teacher' => Mk::userIsClassSectionTeacher(),
        ];

        if ($class_id && $assessment_id && $section_id) {
            $core = $this->getProgressiveCoreData($class_id, $assessments->first()->exam, $section_id, true);
            if ($core === null) {
                return Mk::goWithDanger('assessments.progressive', __('msg.srnf'));
            }
            $data = [...$data, ...$core];
        }

        if (isset($class_id)) {
            $data['class_type_id'] = $this->my_class->findClass($class_id)->class_type_id;
        }

        return view('pages.assessments.progressive.index', $data);
    }

    /**
     * Print progressive assessment.
     */
    public function print_progressive($assessment_id = null, $class_id = null, $section_id = null): View|RedirectResponse
    {
        set_time_limit(300);

        $data['selected'] = false;

        if ($class_id && $assessment_id && $section_id) {
            $core = $this->getProgressiveCoreData($class_id, $assessment_id, $section_id);
            if ($core === null) {
                return Mk::goWithDanger('assessments.progressive', __('msg.srnf'));
            }
            $data = [...$data, ...$core];
        }

        if (isset($class_id)) {
            $data['class_type_id'] = $this->my_class->findClass($class_id)->class_type_id;
        }

        return view('pages.assessments.progressive.print', $data);
    }

    /**
     * Redirect for sheets selection.
     */
    public function sheets_select(Request $req): RedirectResponse
    {
        return redirect()->route('assessments.sheets', [$req->my_class_id, $req->section_id]);
    }

    /**
     * Verify student exam year; show selector or redirect.
     */
    protected function verifyStudentExamYear($student_id, $year = null): View|RedirectResponse|bool
    {
        $years = $this->assessment->getExamYears($student_id);
        $student_exists = $this->student->exists($student_id);

        if ($year === null) {
            return ($student_exists && $years->isNotEmpty())
                ? view('pages.assessments.select_year', ['years' => $years, 'student_id' => Mk::hash($student_id)])
                : $this->noStudentRecord();
        }

        return $student_exists && $years->contains('year', $year);
    }

    /**
     * Show year selector for student assessments.
     */
    public function year_selector($student_id): View|RedirectResponse
    {
        return $this->verifyStudentExamYear($student_id);
    }

    /**
     * Handle year selection and redirect to assessment sheet.
     */
    #[SkipPermission]
    public function year_selected(Request $req, $student_id): RedirectResponse
    {
        $year = $req->year;

        if (!$this->verifyStudentExamYear($student_id, $year)) {
            return $this->noStudentRecord();
        }

        $hashed = Mk::hash($student_id);

        if ($this->assessment->getAssessmentsWithValidExam([])->where('exam.year', $year)->isEmpty()) {
            return redirect()->route('assessments.year_selector', $hashed)
                ->with('flash_info', "Oops! There are no published exam assessments for the selected year $year");
        }

        return redirect()->route('assessments.show', [$hashed, $req->year]);
    }

    /**
     * Authorize access to a student's assessment; enforce locks and PIN.
     */
    protected function authorizeAssessmentAccess($student, $year): ?RedirectResponse
    {
        $user = auth()->user();
        $is_staff = $user->hasAnyRole(Usr::getTenancyAwareStaffRoles());

        if (!$is_staff && $user->id != $student && !Mk::isStudentChildOfParent($student, $user->id)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        if (Mk::examIsLocked() && !$user->hasAnyRole($this->getAdminRoles())) {
            session()->put('assessments_url', route('assessments.show', [Mk::hash($student), $year]));

            if (!$this->checkPinVerified($student)) {
                return redirect()->route('pins.enter', Mk::hash($student));
            }
        }

        if (!$this->verifyStudentExamYear($student, $year)) {
            return $this->noStudentRecord();
        }

        return null; // Allowed
    }

    /**
     * Show a student's assessment details.
     */
    public function show($student_id, $year): View|RedirectResponse
    {
        $redirect = $this->authorizeAssessmentAccess($student_id, $year);
        if ($redirect)
            return $redirect;

        $where = ['student_id' => $student_id, 'year' => $year];
        $asrs = $this->assessment->getAssessmentRecordsWithRelations($where, ['my_class', 'grade']);

        if ($asrs->isEmpty()) {
            return back()->with('flash_danger', __('msg.srnf'));
        }

        $class_type_ids = $asrs->pluck('my_class.class_type_id')->unique()->toArray();
        $assessments = $this->assessment->getAssessmentsWithValidExam(['exam', 'record'])
            ->where('exam.year', $year)
            ->whereIn('exam.class_type_id', $class_type_ids);

        $mc = $this->my_class->getClassesWithSections(['id' => $asrs->value('my_class_id')])->first();

        $data = [
            'assessments_records' => $asrs,
            'assessments' => $assessments,
            'sr' => $this->student->getActiveStudentsQueryWithRelations(['user_id' => $student_id])->first()
                ?? $this->student->getGraduatedStudentsQuery(['user_id' => $student_id])->first(),
            'my_class' => $mc,
            'class_type' => $this->my_class->findClassTypeByClassId($mc->id),
            'subjects' => $this->my_class->findSubjectByClass($mc->id),
            'year' => $year,
            'student_id' => $student_id,
            'skills' => $this->exam->getSkillsByClassType() ?: null,
        ];

        return view('pages.assessments.show.index', $data);
    }

    /**
     * Redirect for progressive selection.
     */
    public function progressive_select(Request $req): RedirectResponse
    {
        return redirect()->route('assessments.progressive', [$req->assessment_id, $req->my_class_id, $req->section_id]);
    }

    /**
     * Update assessment marks.
     */
    public function update(Request $req, $assessment_id, $class_id, $subject_id, $section_id = null): JsonResponse
    {
        $assessment = $this->assessment->getAssessmentsWithValidExam(['id' => $assessment_id])->first();
        if ($assessment->exam->is_locked && !Mk::userIsHead(auth()->id())) {
            return Mk::jsonUpdateDenied();
        }

        $exam = $this->exam->find($assessment->exam->id);

        if ($exam->is_locked && !auth()->user()->hasAnyRole($this->getAdminRoles())) {
            return Mk::jsonUpdateDenied();
        }

        $where = $section_id === null
            ? ['assessment_id' => $assessment_id, 'my_class_id' => $class_id, 'subject_id' => $subject_id, 'year' => $this->year]
            : ['assessment_id' => $assessment_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $this->year];

        $asmnt_records = $this->assessment->getAssessmentRecordsWithRelations($where, ['user', 'exam']);
        $class_type = $this->my_class->findClassTypeByClassId($class_id);
        $input = $req->all();

        $record_updates = [];
        $student_ids = [];

        foreach ($asmnt_records as $as) {
            $student_ids[] = $as->student_id;
            $d = $this->calculateAssessmentRow($as, $input, $assessment->exam, $class_type, $class_id, $subject_id);
            $record_updates[] = $d;
        }

        $this->assessment->massUpdateRecords($record_updates);

        // Update summary/position records
        $summary_updates = [];
        unset($where['subject_id']);
        foreach (array_unique($student_ids) as $st_id) {
            $summary_updates[] = [
                'student_id' => $st_id,
                'average' => $this->assessment->getAverageTermMark($assessment->exam, $st_id, $class_id, $section_id, $this->year),
                'class_average' => $this->assessment->getClassAverageTermMark($assessment->exam, $class_id, $this->year),
                'total' => $this->assessment->getTotalTermMark($assessment->exam, $st_id, $class_id, $this->year),
                'section_position' => $this->assessment->getStudentPositionByColumn($st_id, $assessment->exam->id, $class_id, $this->year, $assessment->exam->ca_student_position_by_value, $section_id),
            ];
        }
        $this->assessment->massUpdateRecords($summary_updates, ['student_id']);

        return Mk::jsonUpdateOk();
    }

    /**
     * Build the per-assessment record update array.
     */
    protected function calculateAssessmentRow($as, array $input, $exam, $class_type, $class_id, $subject_id): array
    {
        $d = ['id' => $as->id];

        // Coursework
        $cw = $this->extractScores($input['cw_'][$as->id] ?? [], self::CW_COUNT);
        for ($i = 1; $i <= self::CW_COUNT; $i++) {
            $d["cw$i"] = $cw['values'][$i] ?? null;
        }
        $cw_avg = $this->avgOrNull($cw['values'], self::CW_COUNT);

        // Homework
        $hw = $this->extractScores($input['hw_'][$as->id] ?? [], self::HW_COUNT);
        for ($i = 1; $i <= self::HW_COUNT; $i++) {
            $d["hw$i"] = $hw['values'][$i] ?? null;
        }
        $hw_avg = $this->avgOrNull($hw['values'], self::HW_COUNT);

        // Topic tests
        $tt = $this->extractScores($input['tt_'][$as->id] ?? [], self::TT_COUNT);
        for ($i = 1; $i <= self::TT_COUNT; $i++) {
            $d["tt$i"] = $tt['values'][$i] ?? null;
        }
        $tt_avg = $this->avgOrNull($tt['values'], self::TT_COUNT);

        $tca = ($cw_avg === null && $hw_avg === null && $tt_avg === null)
            ? null
            : round($cw_avg + $hw_avg + $tt_avg, 1);

        $exam = $input["exm_{$as->id}"] ?? null;
        $d['tca'] = $tca;
        $d['evaluated_exam_mark'] = $exam;

        $total = ($tca === null && $exam === null) ? null : $tca + $exam;
        $term = $exam->term;
        $d["tex$term"] = ($total > 100) ? (function () use (&$d) {
            $d['t1'] = $d['t2'] = $d['t3'] = $d['t4'] = $d['tca'] = $d['evaluated_exam_mark'] = null;
            return null;
        })() : $total;

        $grade = $this->mark->getGradeByTotal($total, $class_type->id);
        $d['grade_id'] = $grade->id ?? null;
        $d['subject_position'] = $this->assessment->getSubjectPosition($as->student_id, $exam, $class_id, $subject_id, $this->year);

        return $d;
    }

    /**
     * Extract scores from input array for a given component, padding with nulls.
     */
    protected function extractScores(array $input, int $count): array
    {
        $values = [];
        for ($i = 1; $i <= $count; $i++) {
            $values[$i] = $input[$i] ?? null;
        }
        return ['values' => $values];
    }

    /**
     * Compute average of non-null values; return null if all null.
     */
    protected function avgOrNull(array $values, int $count): ?float
    {
        $sum = 0;
        $notNull = false;
        for ($i = 1; $i <= $count; $i++) {
            if ($values[$i] !== null) {
                $sum += $values[$i];
                $notNull = true;
            }
        }
        return $notNull ? $sum / $count : null;
    }

    /**
     * Print assessment sheet for a specific exam/class/subject.
     */
    public function print($assessment_id, $class_id, $subject_id, $year, $section_id = null): View|RedirectResponse
    {
        $assessment = $this->assessment->getAssessmentsWithValidExam(['id' => $assessment_id])->first();
        $d = $section_id === null
            ? ['exam_id' => $assessment->exam->id, 'my_class_id' => $class_id, 'subject_id' => $subject_id, 'year' => $year]
            : ['exam_id' => $assessment->exam->id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $year];

        $marks_updated_at = $this->mark->getMarkValue($d, 'updated_at');
        $asmnts_updated_at = $this->assessment->getAssessmentRecordValue($d, 'updated_at');

        $assessment_records = $this->assessment->getAssessmentRecordsWithRelations($d, ['exam', 'user', 'subject', 'my_class', 'section', 'grade']);

        if ($assessment_records->isEmpty()) {
            return $this->noStudentRecord();
        }

        if (strtotime($marks_updated_at) >= strtotime($asmnts_updated_at)) {
            return back()->with('flash_info', __('msg.update_assessments'));
        }

        $wh = $section_id === null
            ? ['my_class_id' => $class_id, 'assessment_id' => $assessment->id, 'year' => $this->year]
            : ['my_class_id' => $class_id, 'section_id' => $section_id, 'assessment_id' => $assessment->id, 'year' => $year];

        $tex = "tex{$assessment->exam->term}";

        return view('pages.assessments.print', [
            'assessments' => $assessment_records->sortBy('user.name')->sortBy('user.gender'),
            'm' => $assessment_records->first(),
            'exam' => $assessment->exam,
            'my_class' => $this->my_class->findClass($class_id),
            'selected' => true,
            'asr' => $this->assessment->getAssessmentRecordsWithRelations($wh, ['exam', 'user', 'subject', 'my_class', 'section']),
            'tex' => $tex,
            'stds_texs' => $this->assessment->getTermTotalsDescending($d, $tex),
            'section_id' => $section_id,
        ]);
    }

    /**
     * Print detailed assessment for a student.
     */
    public function print_detailed($student_id, $assessment_id, $year): View|RedirectResponse
    {
        $redirect = $this->authorizeAssessmentAccess($student_id, $year);
        if ($redirect)
            return $redirect;

        $wh = ['student_id' => $student_id, 'assessment_id' => $assessment_id, 'year' => $year];
        $asrs = $this->assessment->getAssessmentRecordsWithRelations($wh);

        if ($asrs->isEmpty()) {
            return back()->with('flash_danger', __('msg.srnf'));
        }

        $asr = $asrs->first();
        $mc = $this->my_class->findClass($asr->my_class_id);
        $class_type = $this->my_class->findClassTypeByClassId($mc->id);

        return view('pages.assessments.print.detailed', [
            'assessments_records' => $asrs,
            'asr' => $asr,
            'my_class' => $mc,
            'section' => $this->my_class->findSection($asr->section_id),
            'tex' => "tex{$asr->exam->term}",
            'sr' => $this->student->getActiveStudentsQueryWithRelations(['user_id' => $student_id])->first(),
            'class_type' => $class_type,
            'subjects' => $this->my_class->findSubjectByClass($mc->id),
            'ct' => $class_type->code,
            'year' => $year,
            'student_id' => $student_id,
            'assessment_id' => $assessment_id,
            'skills' => $this->exam->getSkillsByClassType() ?: null,
        ]);
    }

    /**
     * Print minimal assessment for a student.
     */
    public function print_minimal($student_id, $assessment_id, $year): View|RedirectResponse
    {
        $redirect = $this->authorizeAssessmentAccess($student_id, $year);
        if ($redirect)
            return $redirect;

        $wh = ['student_id' => $student_id, 'assessment_id' => $assessment_id, 'year' => $year];
        $asrs = $this->assessment->getAssessmentRecordsWithRelations($wh);

        if ($asrs->isEmpty()) {
            return back()->with('flash_danger', __('msg.srnf'));
        }

        $asr = $asrs->first();
        $mc = $this->my_class->findClass($asr->my_class_id);

        return view('pages.assessments.print.minimal', [
            'assessments_records' => $asrs,
            'asr' => $asr,
            'my_class' => $mc,
            'section' => $this->my_class->findSection($asr->section_id),
            'tex' => "tex{$asr->exam->term}",
            'sr' => $this->student->getActiveStudentsQueryWithRelations(['user_id' => $student_id])->first(),
            'subjects' => $this->my_class->findSubjectByClass($mc->id),
            'year' => $year,
            'skills' => $this->exam->getSkillsByClassType() ?: null,
        ]);
    }

    /**
     * Redirect to dashboard with student not found message.
     */
    protected function noStudentRecord(): RedirectResponse
    {
        return redirect()->route('dashboard')->with('flash_danger', __('msg.srnf'));
    }

    /**
     * Redirect to dashboard with assessment not found message.
     */
    protected function noAssessmentRecord(): RedirectResponse
    {
        return redirect()->route('dashboard')->with('flash_danger', __('msg.arnf'));
    }

    /**
     * Check if PIN verification session exists for a student.
     */
    protected function checkPinVerified($st_id): bool
    {
        return Session::has('pin_verified') && Session::get('pin_verified') == $st_id;
    }

    /**
     * Print cover page for a student.
     */
    public function print_cover($student): View
    {
        $data['student'] = $this->student->getActiveStudentsQueryWithRelations(["user_id" => $student], ['user'])->first();
        return view('pages/assessments/print/cover', $data);
    }

    /**
     * Update teacher/principal comment on an assessment record.
     */
    public function comment_update(Request $req, $as_id): JsonResponse
    {
        $d = auth()->user()->hasAnyRole($this->getAdminRoles())
            ? $req->only(['t_comment', 'p_comment'])
            : $req->only(['t_comment']);

        $this->assessment->updateRecord(['id' => $as_id], $d);
        return Mk::jsonUpdateOk();
    }

    /**
     * Update AF/PS skills on an assessment record.
     */
    public function skills_update(Request $req, $skill, $as_id): JsonResponse
    {
        $d = [];
        if ($skill === 'AF' || $skill === 'PS') {
            $d[strtolower($skill)] = implode(',', $req->{strtolower($skill)});
        }

        $this->assessment->updateRecord(['id' => $as_id], $d);
        return Mk::jsonUpdateOk();
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
     * Build the core data used by both progressive and print_progressive views.
     * Returns null if required data is missing (calling methods then redirect).
     */
    protected function getProgressiveCoreData(int $class_id, $exam, int $section_id, bool $validateExamClassType = false): ?array
    {
        if ($validateExamClassType) {
            $class_type = $this->my_class->findClassTypeByClassId($class_id);
            $examBelongsToClass = $this->assessment->getAssessmentsWithValidExam([])
                ->where('exam.class_type_id', $class_type->id)
                ->where('exam_id', $exam->id)
                ->isNotEmpty();

            if (!$examBelongsToClass) {
                return null;
            }
        }

        $where = [
            'my_class_id' => $class_id,
            'section_id' => $section_id,
            'exam_id' => $exam->id,
            'year' => $this->year,
        ];

        $sub_ids = $this->mark->getDistinctSubjectIds($where);
        $st_ids = $this->mark->getDistinctStudentIds($where);
        $asr = $this->assessment->getAssessmentRecordsWithRelations($where, ['user', 'exam', 'grade']);

        if ($sub_ids->isEmpty() || $st_ids->isEmpty() || $asr->isEmpty()) {
            return null;
        }

        $class_type_id = $this->my_class->findClass($class_id)->class_type_id; // get from previous
        $subjects = $this->my_class->getSubjectsByIds($sub_ids);

        $sections = Mk::userIsClassSectionTeacher()
            ? $this->my_class->getTeacherClassSections($class_id, auth()->id())
            : $this->my_class->getAllSections();

        return [
            'students_summary' => $this->buildProgressiveStudentData($asr, $subjects, "tex{$exam->term}", $class_type_id),
            'students' => $this->student->getStudentsByUserIds($st_ids->toArray())
                ->get()
                ->whereNotNull('user')
                ->sortBy('user.name')
                ->sortBy('user.gender'),
            'subjects' => $subjects,
            'sections' => $sections,
            'selected' => true,
            'my_class_id' => $class_id,
            'section_id' => $section_id,
            'exam_id' => $exam->id,
            'year' => $this->year,
            'asr' => $asr,
            'my_class' => $this->my_class->findClass($class_id),
            'section' => $this->my_class->findSection($section_id),
            'ex' => $exam,
            'tex' => "tex{$exam->term}",
            'grades' => $this->exam->getGrades()->pluck('name'),
        ];
    }

    /**
     * Pre‑compute per‑student totals, averages, grades, positions, and subject marks.
     */
    protected function buildProgressiveStudentData($asr, $subjects, $tex, $class_type_id): array
    {
        $student_summaries = [];

        $asr_grouped = $asr->groupBy('student_id');

        foreach ($asr_grouped as $student_id => $records) {
            $summary = $records->first(); // Contains total, average, section_position, etc.

            $sub_marks = [];
            foreach ($subjects as $sub) {
                $mark = $records->where('subject_id', $sub->id)->first();
                $sub_marks[$sub->id] = [
                    'mark' => $mark->$tex ?? null,
                    'grade' => $mark->grade->name ?? null,
                    'pos' => $mark->subject_position ?? null,
                ];
            }

            $student_summaries[$student_id] = [
                'name' => optional($summary->user)->name,
                'gender' => optional($summary->user)->gender,
                'total' => $summary->total,
                'average' => $summary->average,
                'grade' => $summary->grade_id ? $this->mark->getGradeByTotal($summary->average, $class_type_id)->name ?? null : null,
                'pos' => $summary->section_position,
                'sub_marks' => $sub_marks,
            ];
        }

        return $student_summaries;
    }
}
