<?php

namespace App\Http\Controllers;

use App\Helpers\Mk;
use App\Helpers\Usr;
use App\Http\Requests\Assessment\AssessmentSelector;
use App\Models\Assessment;
use App\Models\AssessmentRecord;
use App\Models\ClassType;
use App\Models\Exam;
use App\Repositories\AssessmentRepo;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;
use PermissionKit\Attributes\SkipPermission;
use PermissionKit\Concerns\AuthorizesPermissions;

class AssessmentController extends Controller
{
    use AuthorizesPermissions;

    protected string $year;

    protected const int CW_COUNT = 10;

    protected const int HW_COUNT = 5;

    protected const int TT_COUNT = 3;

    public function __construct(
        protected ExamRepo $exam,
        protected AssessmentRepo $assessment,
        protected MyClassRepo $my_class,
        protected StudentRepo $student,
        protected MarkRepo $mark
    ) {
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
            ? $this->student->getActiveStudentsByUserIdsQuery($sub_record->students_ids)->get()
            : $this->student->getActiveStudentsQueryWithRelations($classSection)->get();

        if ($students->isEmpty()) {
            return back()->with('pop_error', __('msg.rnf'));
        }

        return redirect()->route('assessments.manage', [
            $req->assessment_id,
            $req->my_class_id,
            $req->subject_id,
            $req->section_id,
        ]);
    }

    /**
     * Manage assessments for a given exam/class/subject.
     */
    public function manage(int $assessment_id, int $class_id, int $subject_id, ?int $section_id = null): View|RedirectResponse
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
            'exam' => $first->exam,
            'my_class_id' => $class_id,
            'subject_id' => $subject_id,
            'year' => $this->year,
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
        if (! $this->assessment->hasAnyAssessmentWithExam()) {
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
            'teacher_class_ids' => $this->my_class->getSectionsByConditions(['teacher_id' => auth()->id()])->pluck('my_class_id'),
            'selected' => false,
            'user_is_class_section_teacher' => Mk::userIsClassSectionTeacher(),
        ];

        if ($class_id && $assessment_id && $section_id) {
            $core = $this->getProgressiveCoreData($class_id, $assessments->first(), $section_id, true);
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
            $core = $this->getProgressiveCoreData($class_id, $this->assessment->getAssessmentByConditions(['id' => $assessment_id]), $section_id);
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
    protected function verifyStudentExamYear(int $student_id, ?string $year = null): View|RedirectResponse|bool
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
    public function year_selector(int $student_id): View|RedirectResponse
    {
        return $this->verifyStudentExamYear($student_id);
    }

    /**
     * Handle year selection and redirect to assessment sheet.
     */
    #[SkipPermission]
    public function year_selected(Request $req, int $student_id): RedirectResponse
    {
        $year = $req->year;

        if (! $this->verifyStudentExamYear($student_id, $year)) {
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
    protected function authorizeAssessmentAccess(int $student, string $year): ?RedirectResponse
    {
        $user = auth()->user();
        $is_staff = $user->hasAnyRole(Usr::getTenancyAwareStaffRoles());

        if (! $is_staff && $user->id != $student && ! Mk::isStudentChildOfParent($student, $user->id)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        if (Mk::examIsLocked() && ! $user->hasAnyRole($this->getAdminRoles())) {
            session()->put('assessments_url', route('assessments.show', [Mk::hash($student), $year]));

            if (! $this->checkPinVerified($student)) {
                return redirect()->route('pins.enter', Mk::hash($student));
            }
        }

        if (! $this->verifyStudentExamYear($student, $year)) {
            return $this->noStudentRecord();
        }

        return null; // Allowed
    }

    /**
     * Show a student's assessment details.
     */
    public function show(int $student_id, string $year): View|RedirectResponse
    {
        $redirect = $this->authorizeAssessmentAccess($student_id, $year);
        if ($redirect) {
            return $redirect;
        }

        $where = ['student_id' => $student_id, 'year' => $year];
        $asrs = $this->assessment->getAssessmentRecordsWithRelations($where, ['my_class', 'grade']);

        if ($asrs->isEmpty()) {
            return back()->with('flash_danger', __('msg.srnf'));
        }

        $class_type_ids = $asrs->pluck('my_class.class_type_id')->unique()->toArray();
        $assessments = $this->assessment->getAssessmentsWithValidExam([], ['exam', 'record'])
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
     *
     * Positions are always recomputed class-wide. The window functions in
     * AssessmentRepo do section-level partitioning internally, so a WHERE
     * section filter here would produce section-wide ranks and diverge
     * from the batch path.
     *
     * The summary average uses the student's own section (derived from the
     * already-loaded records) rather than the request's section, so the
     * value broadcast to all of the student's rows is consistent regardless
     * of whether the caller edited one section or the whole class.
     */
    public function update(
        Request $req,
        int $assessment_id,
        int $class_id,
        int $subject_id,
        ?int $section_id = null
    ): JsonResponse {
        $assessment = $this->assessment->getAssessmentsWithValidExam(['id' => $assessment_id])->first();
        if (! $assessment) {
            return Mk::json(__('msg.arnf'), false);
        }
        if ($assessment->exam->is_locked && ! Mk::userIsHead(auth()->id())) {
            return Mk::jsonUpdateDenied();
        }
        $exam = $this->exam->find($assessment->exam->id);
        if ($exam->is_locked && ! auth()->user()->hasAnyRole($this->getAdminRoles())) {
            return Mk::jsonUpdateDenied();
        }

        $where = $section_id === null
            ? ['assessment_id' => $assessment_id, 'my_class_id' => $class_id, 'subject_id' => $subject_id, 'year' => $this->year]
            : ['assessment_id' => $assessment_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $this->year];

        $asmnt_records = $this->assessment->getAssessmentRecordsWithRelations($where, ['user', 'exam']);
        $class_type = $this->my_class->findClassTypeByClassId($class_id);
        $input = $req->all();

        DB::transaction(function () use ($asmnt_records, $assessment_id, $input, $assessment, $class_type, $class_id, $subject_id, $section_id) {
            // ── 1. Detail rows (per subject) written by PK ─────────────
            $record_updates = [];
            foreach ($asmnt_records as $as) {
                $record_updates[] = $this->calculateAssessmentRow(
                    $as,
                    $input,
                    $assessment->exam,
                    $class_type,
                    $class_id,
                    $subject_id
                );
            }
            if ($record_updates) {
                $this->assessment->massUpdateRecords($record_updates, ['id']);
            }

            // ── 2. Summary: ONE update per student, no upsert ─────────
            // Summary values are duplicated on every subject row of a
            // student, so we broadcast a single consistent value to all
            // of them. Use updateRecord (a real SQL UPDATE) so we never
            // insert duplicate rows.
            $student_ids = $asmnt_records->pluck('student_id')->unique()->values()->all();

            $classAvg = $this->assessment->getClassAverageTermMark($assessment, $class_id, $this->year);

            foreach ($student_ids as $st_id) {
                // Prefer the request's section when it was provided;
                // otherwise derive the student's own section from the
                // loaded records so the average isn't filtered to NULL.
                $studentSection = $section_id
                    ?? $asmnt_records->firstWhere('student_id', $st_id)?->section_id;

                $this->assessment->updateRecord(
                    [
                        'assessment_id' => $assessment_id,
                        'student_id' => $st_id,
                        'my_class_id' => $class_id,
                        'year' => $this->year,
                    ],
                    [
                        'average' => $this->assessment->getAverageTermMark($assessment, $st_id, $class_id, $studentSection, $this->year),
                        'class_average' => $classAvg,
                        'total' => $this->assessment->getTotalTermMark($assessment, $st_id, $class_id, $this->year),
                    ]
                );
            }

            // ── 3. Window-function positions — always class-wide ──────
            $this->assessment->refreshSubjectPositions(
                $assessment_id,
                $class_id,
                $this->year,
                $assessment->exam->term
            );

            $this->assessment->refreshSummaryPositions(
                $assessment_id,
                $class_id,
                $this->year,
                $assessment->exam->ca_student_position_by_value,
                $assessment->exam->ca_student_position_sort_order,
                bySection: true
            );

            $this->assessment->refreshSummaryPositions(
                $assessment_id,
                $class_id,
                $this->year,
                $assessment->exam->ca_student_position_by_value,
                $assessment->exam->ca_student_position_sort_order,
                bySection: false
            );
        });

        return Mk::jsonUpdateOk();
    }

    /**
     * Build the per-assessment record update array.
     */
    protected function calculateAssessmentRow(
        AssessmentRecord $as,
        array $input,
        Exam $exam,
        ClassType $class_type,
        int $class_id,
        int $subject_id
    ): array {
        $d = ['id' => $as->id];

        $cw = $this->extractScores($input['cw_'][$as->id] ?? [], self::CW_COUNT);
        for ($i = 1; $i <= self::CW_COUNT; $i++) {
            $d["cw$i"] = $cw['values'][$i] ?? null;
        }
        $cw_avg = $this->avgOrNull($cw['values'], self::CW_COUNT);

        $hw = $this->extractScores($input['hw_'][$as->id] ?? [], self::HW_COUNT);
        for ($i = 1; $i <= self::HW_COUNT; $i++) {
            $d["hw$i"] = $hw['values'][$i] ?? null;
        }
        $hw_avg = $this->avgOrNull($hw['values'], self::HW_COUNT);

        $tt = $this->extractScores($input['tt_'][$as->id] ?? [], self::TT_COUNT);
        for ($i = 1; $i <= self::TT_COUNT; $i++) {
            $d["tt$i"] = $tt['values'][$i] ?? null;
        }
        $tt_avg = $this->avgOrNull($tt['values'], self::TT_COUNT);

        $tca = ($cw_avg === null && $hw_avg === null && $tt_avg === null)
            ? null
            : round(($cw_avg ?? 0) + ($hw_avg ?? 0) + ($tt_avg ?? 0), 1);

        $exm_mark = $input["exm_{$as->id}"] ?? null;

        $d['tca'] = $tca;
        $d['evaluated_exam_mark'] = $exm_mark;

        $total = ($tca === null && $exm_mark === null) ? null : ($tca ?? 0) + ($exm_mark ?? 0);
        $term = $exam->term;

        if ($total !== null && $total > 100) {
            $d['t1'] = $d['t2'] = $d['t3'] = $d['t4'] = null;
            $d['tca'] = $d['evaluated_exam_mark'] = null;
            $d["tex{$term}"] = null;
        } else {
            $d["tex{$term}"] = $total;
        }

        $grade = $total !== null ? $this->mark->getGradeByTotal($total, $class_type->id) : null;
        $d['grade_id'] = $grade->id ?? null;

        return $d; // subject_position filled later by window function
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
    public function print(int $assessment_id, int $class_id, int $subject_id, string $year, ?int $section_id = null): View|RedirectResponse
    {
        $assessment = $this->assessment->getAssessmentsWithValidExam(['id' => $assessment_id])->first();
        $d = $section_id === null
            ? ['my_class_id' => $class_id, 'subject_id' => $subject_id, 'year' => $year]
            : ['my_class_id' => $class_id, 'section_id' => $section_id, 'subject_id' => $subject_id, 'year' => $year];

        $marks_updated_at = $this->mark->getMarkValue([...$d, 'exam_id' => $assessment->exam->id], 'updated_at');
        $asmnts_updated_at = $this->assessment->getAssessmentRecordValue([...$d, 'assessment_id' => $assessment_id], 'updated_at');

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
            'year' => $year,
        ]);
    }

    /**
     * Print detailed assessment for a student.
     */
    public function print_detailed(int $student_id, int $assessment_id, string $year): View|RedirectResponse
    {
        $redirect = $this->authorizeAssessmentAccess($student_id, $year);
        if ($redirect) {
            return $redirect;
        }

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
    public function print_minimal(int $student_id, int $assessment_id, string $year): View|RedirectResponse
    {
        $redirect = $this->authorizeAssessmentAccess($student_id, $year);
        if ($redirect) {
            return $redirect;
        }

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
    protected function checkPinVerified(int $st_id): bool
    {
        return Session::has('pin_verified') && Session::get('pin_verified') == $st_id;
    }

    /**
     * Print cover page for a student.
     */
    public function print_cover(int $student): View
    {
        $data['student'] = $this->student->getActiveStudentsQueryWithRelations(['user_id' => $student], ['user'])->first();

        return view('pages.assessments.print.cover', $data);
    }

    /**
     * Update teacher/principal comment on an assessment record.
     */
    public function comment_update(Request $req, int $as_id): JsonResponse
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
    public function skills_update(Request $req, string $skill, int $as_id): JsonResponse
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
    protected function getProgressiveCoreData(int $class_id, Assessment $assessment, int $section_id, bool $validateExamClassType = false): ?array
    {
        if ($validateExamClassType) {
            $class_type = $this->my_class->findClassTypeByClassId($class_id);

            $examBelongsToClass = $this->assessment->getAssessmentsWithValidExam(['id' => $assessment->id])
                ->where('exam.class_type_id', $class_type->id)
                ->isNotEmpty();

            if (! $examBelongsToClass) {
                return null;
            }
        }

        $where = [
            'my_class_id' => $class_id,
            'section_id' => $section_id,
            'exam_id' => $assessment->exam->id,
            'year' => $this->year,
        ];

        $sub_ids = $this->mark->getDistinctSubjectIds($where);
        $st_ids = $this->mark->getDistinctStudentIds($where);
        $asr = $this->assessment->getAssessmentRecordsWithRelations([
            'my_class_id' => $class_id,
            'section_id' => $section_id,
            'assessment_id' => $assessment->id,
            'year' => $this->year,
        ], ['user', 'exam', 'grade']);

        if ($sub_ids->isEmpty() || $st_ids->isEmpty() || $asr->isEmpty()) {
            return null;
        }

        $class_type_id = $this->my_class->findClass($class_id)->class_type_id;
        $subjects = $this->my_class->getSubjectsByIds($sub_ids);

        $sections = Mk::userIsClassSectionTeacher()
            ? $this->my_class->getTeacherClassSections($class_id, auth()->id())
            : $this->my_class->getAllSections();

        $tex = "tex{$assessment->exam->term}";

        return [
            'students_summary' => $this->buildProgressiveStudentData($asr, $subjects, $tex, $class_type_id),
            'students' => $this->student->getStudentsByUserIdsQuery($st_ids->toArray())
                ->get()
                ->whereNotNull('user')
                ->sortBy('user.name')
                ->sortBy('user.gender'),
            'subjects' => $subjects,
            'sections' => $sections,
            'selected' => true,
            'my_class_id' => $class_id,
            'section_id' => $section_id,
            'year' => $this->year,
            'asr' => $asr,
            'my_class' => $this->my_class->findClass($class_id),
            'section' => $this->my_class->findSection($section_id),
            'tex' => $tex,
            'grades' => $this->exam->getGrades()->pluck('name'),
            'assessment' => $assessment,
        ];
    }

    /**
     * Pre‑compute per‑student totals, averages, grades, positions, and subject marks.
     */
    protected function buildProgressiveStudentData(Collection $asr, Collection $subjects, string $tex, int $class_type_id): array
    {
        $student_summaries = [];

        $asr_grouped = $asr->groupBy('student_id');

        foreach ($asr_grouped as $student_id => $records) {
            $summary = $records->first();

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
