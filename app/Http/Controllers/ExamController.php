<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Helpers\Mk;
use App\Helpers\Usr;
use App\Http\Requests\Exam\ExamAnnounce;
use App\Http\Requests\Exam\ExamCreate;
use App\Http\Requests\Exam\ExamUpdate;
use App\Http\Requests\Exam\ExamUpdateLockState;
use App\Models\Exam;
use App\Models\MyClass;
use App\Repositories\AssessmentRepo;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Rules\HasStudentExamNumberPlaceholder;
use App\Rules\Uppercase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ExamController extends Controller
{
    protected string $year;

    public function __construct(protected ExamRepo $exam, protected AssessmentRepo $assessment, protected MyClassRepo $my_class, protected StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.exams');
        parent::__construct();
        $this->year = Mk::getSetting('current_session');
    }

    /**
     * Display a listing of exams.
     */
    public function index(): View
    {
        $data['exams'] = $this->exam->getAllExams(['category']);
        $data['exams_cat'] = $this->exam->getCategories();
        $data['class_types'] = $this->exam->getAllClassTypes();
        $data['current_session'] = $this->year;

        return view('pages.exams.index', $data);
    }

    /**
     * Store a newly created exam.
     */
    public function store(ExamCreate $req): JsonResponse
    {
        $class_type_id = $req->class_type_id;
        $data = $req->only($this->getExamDataKeys($req));
        $data['year'] = $this->year;

        $exam = $this->exam->firstOrCreate($data);
        $classes = $this->my_class->getClassesByConditions(['class_type_id' => $class_type_id]);

        $this->validateAndSaveNumberFormats($req, $classes, $exam->id);

        if ($req->boolean('has_assessment')) {
            $this->createAssessmentForExam($exam->id);
        }

        return Mk::jsonStoreOk();
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(int $id): View
    {
        $data['ex'] = $this->exam->findExam($id, false, ['category', 'class_type']);
        $data['exams_cat'] = $this->exam->getCategories();

        return view('pages.exams.edit', $data);
    }

    /**
     * Update the specified exam.
     */
    public function update(ExamUpdate $req, int $id): JsonResponse
    {
        $exam = $this->exam->getExamsByConditions(['id' => $id], false, ['assessment'])->first();

        if ($exam->is_locked) {
            return Mk::jsonUpdateDenied();
        }

        $data = $req->only($this->getExamDataKeys($req));
        $this->exam->update($id, $data);

        $classes = $this->my_class->getClassesByConditions(['class_type_id' => $exam->class_type_id]);
        $append_message = '';
        if ($classes->isEmpty()) {
            $append_message = " But no classes were found for the class type {$exam->class_type->name}, so the number formats were not updated.";
        } else {
            $this->validateAndUpdateNumberFormats($req, $classes, $exam->id);
        }
        $this->handleAssessmentForExam($req, $exam);

        return Mk::json(__('msg.update_ok').$append_message);
    }

    /**
     * Remove the specified exam.
     */
    public function destroy(int $id): RedirectResponse
    {
        $exam = $this->exam->findExam($id);
        if ($exam->is_locked) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $this->exam->delete($id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Show the Exam Numbers page.
     */
    public function assign_view(): View
    {
        $data['my_classes'] = $this->my_class->getAllClasses();
        $data['exams'] = $this->exam->getExamsByConditions(['year' => $this->year]);
        $data['year'] = $this->year;

        return view('pages.exams.assign', $data);
    }

    /**
     * AJAX: render the exam numbers table.
     */
    #[SkipPermission]
    public function exam_numbers_load(Request $request): View
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:my_classes,id',
        ]);

        $exam_id = $request->input('exam_id');
        $class_id = $request->input('class_id');
        $section_id = $request->input('section_id') ?: null;

        $students = $this->student->getActiveStudentsByClass($class_id, ['user', 'section'])
            ->when($section_id, fn ($q) => $q->where('section_id', $section_id))
            ->sortBy('user.name');

        $numbers = $this->exam->getExamRecords(['exam_id' => $exam_id, 'my_class_id' => $class_id])
            ->pluck('exam_number', 'student_id');

        return view('pages.exams.partials.exam_numbers', compact('students', 'numbers'));
    }

    /**
     * AJAX: save manually edited exam numbers.
     */
    public function exam_numbers_save(Request $request): JsonResponse
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:my_classes,id',
            'numbers' => 'required|array',
        ]);

        $exam_id = $request->input('exam_id');
        $class_id = $request->input('class_id');

        DB::transaction(function () use ($request, $exam_id, $class_id) {
            foreach ($request->input('numbers', []) as $student_id => $number) {
                $this->exam->updateOrCreateExamRecord(
                    [
                        'exam_id' => $exam_id,
                        'student_id' => $student_id,
                        'my_class_id' => $class_id,
                    ],
                    ['exam_number' => $number ?: null]
                );
            }
        });

        return Mk::jsonUpdateOk();
    }

    /**
     * Update the locked state of an exam.
     */
    public function update_lock_state(ExamUpdateLockState $req): JsonResponse
    {
        $exam = $this->exam->findExam($req->id, false);
        if ($exam->is_published) {
            return Mk::jsonUpdateDenied();
        }

        $this->exam->update($req->id, $req->only('is_locked'));

        return Mk::json('ok');
    }

    /**
     * Publish an exam.
     */
    public function publish(int $exam_id): RedirectResponse
    {
        $this->exam->update($exam_id, ['is_published' => true, 'is_locked' => true]);
        $exam = $this->exam->findExam($exam_id);

        // Freeze every grade and division used by this exam
        $this->exam->lockGradesAndDivisionsForExam($exam_id);

        return back()
            ->with('flash_success', __('msg.exam_published'))
            ->with('announce_exam', true)
            ->with('exam_id', $exam_id)
            ->with('exam_name', $exam->name);
    }

    /**
     * Announce an exam.
     */
    public function announce(ExamAnnounce $req): JsonResponse
    {
        $data = $req->only(['exam_id', 'message', 'duration']);
        $data['duration'] += time();
        $this->exam->announce($data);

        return Mk::jsonStoreOk();
    }

    /**
     * Generate exam numbers for students of a class/section based on the exam's number format.
     */
    public function generate_exam_numbers(Request $request)
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'my_class_id' => 'required|exists:my_classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $exam = $this->exam->findExam($validated['exam_id'], false, ['number_format']);
        $class = $this->my_class->findClass($validated['my_class_id']);
        $section_id = $validated['section_id'] ?? null;

        // Get the number format for this exam + class
        $format = $exam->number_format?->where('my_class_id', $class->id)->first();
        if (! $format) {
            return response()->json(['msg' => "Number format not found for the exam $exam->name and class $class->name", 'ok' => false]);
        }

        // Fetch students
        $students = $this->student->getActiveStudentsQueryWithRelations(['my_class_id' => $class->id], ['user'])
            ->when($section_id, fn ($q) => $q->where('section_id', $section_id))
            ->get()
            ->whereNotNull('user')
            ->sortBy('user.name')
            ->sortBy('user.gender');

        if ($students->isEmpty()) {
            return response()->json(['msg' => __('msg.srnf'), 'ok' => false]);
        }

        $placeholder = Usr::getStudentExamNumberPlaceholder();
        $format_str = $format->format;
        $counter = 0;
        $generated = 0;

        foreach ($students as $student) {
            // Count placeholders
            $placeholder_count = substr_count($format_str, $placeholder);
            if ($placeholder_count === 0) {
                continue; // no placeholder – can't generate dynamic number
            }
            $counter++;
            $padded_number = str_pad($counter, $placeholder_count, '0', STR_PAD_LEFT);
            $exam_number = str_replace(str_repeat($placeholder, $placeholder_count), $padded_number, $format_str);

            // Update or create the exam record with the generated number
            $this->exam->updateOrCreateExamRecord(
                [
                    'exam_id' => $exam->id,
                    'student_id' => $student->user_id,
                    'my_class_id' => $class->id,
                    'section_id' => $student->section_id,
                    'year' => $exam->year,
                ],
                ['exam_number' => $exam_number]
            );
            $generated++;
        }

        return Usr::json(__('msg.update_ok')." Generated: ({$generated} / {$counter})", true);
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Get exam data keys based on whether assessment is enabled.
     */
    protected function getExamDataKeys(Request $req): array
    {
        $has_assessment = $req->boolean('has_assessment');

        return $has_assessment
            ? Mk::getExamFieldNames()
            : Mk::getExamFieldNames(['ca_student_position_by_value', 'ca_student_position_sort_order', 'cw_denominator', 'hw_denominator', 'tt_denominator', 'tdt_denominator']);
    }

    /**
     * Validate and save number formats for each class during exam creation.
     */
    protected function validateAndSaveNumberFormats(Request $req, Collection $classes, int $exam_id): void
    {
        foreach ($classes as $class) {
            $this->validateNumberFormat($req, $class);

            $format = $req->input("exam_number_format_{$class->id}");
            if ($format !== null) {
                $this->exam->createNumberFormat([
                    'exam_id' => $exam_id,
                    'my_class_id' => $class->id,
                    'format' => $format,
                ]);
            }
        }
    }

    /**
     * Validate and update/delete number formats for each class during exam update.
     */
    protected function validateAndUpdateNumberFormats(Request $req, Collection $classes, int $exam_id): void
    {
        foreach ($classes as $class) {
            $this->validateNumberFormat($req, $class);

            $format = $req->input("exam_number_format_{$class->id}");
            $number_format_id = $req->input("number_format_{$class->id}");

            if ($format !== null) {
                $this->exam->updateOrCreateNumberFormat(
                    ['id' => $number_format_id],
                    ['exam_id' => $exam_id, 'my_class_id' => $class->id, 'format' => $format]
                );
            } elseif ($format === null && $number_format_id !== null) {
                $this->exam->deleteNumberFormat($number_format_id);
            }
        }
    }

    /**
     * Validate number format input for a single class.
     */
    protected function validateNumberFormat(Request $req, MyClass $class): void
    {
        Validator::make(
            $req->toArray(),
            [
                "class_id_{$class->id}" => "nullable|required_unless:exam_number_format_{$class->id},null",
                "exam_number_format_{$class->id}" => ['nullable', 'regex:/^[\p{L}\p{N}\/\\-*]+$/u', "required_unless:class_id_{$class->id},null", new Uppercase, new HasStudentExamNumberPlaceholder],
            ],
            [
                "exam_number_format_{$class->id}.required_unless" => "The {$class->name} exam number format field is required unless {$class->name} class is in not checked.",
            ],
            [
                "class_id_{$class->id}" => "{$class->name} class",
                "exam_number_format_{$class->id}" => "{$class->name} exam number format",
            ]
        )->validate();
    }

    /**
     * Create assessment record for an exam.
     */
    protected function createAssessmentForExam(int $exam_id): void
    {
        $assessment = $this->assessment->firstOrCreate(['exam_id' => $exam_id]);
        $this->exam->firstOrCreateExamAssessment(['exam_id' => $exam_id, 'assessment_id' => $assessment->id]);
    }

    /**
     * Handle assessment creation or deletion based on request input.
     */
    protected function handleAssessmentForExam(Request $req, Exam $exam): void
    {
        if ($req->boolean('has_assessment')) {
            $this->createAssessmentForExam($exam->id);
        } elseif ($exam->assessment !== null) {
            $this->assessment->delete($exam->assessment->id);
        }
    }
}
