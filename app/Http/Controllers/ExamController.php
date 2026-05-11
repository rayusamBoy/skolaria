<?php

namespace App\Http\Controllers;

use App\Helpers\Mk;
use App\Http\Controllers\Controller;
use App\Http\Requests\Exam\ExamAnnounce;
use App\Http\Requests\Exam\ExamCreate;
use App\Http\Requests\Exam\ExamUpdate;
use App\Http\Requests\Exam\ExamUpdateLockState;
use App\Repositories\AssessmentRepo;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Rules\HasStudentExamNumberPlaceholder;
use App\Rules\Uppercase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ExamController extends Controller
{
    protected ExamRepo $exam;
    protected AssessmentRepo $assessment;
    protected MyClassRepo $my_class;
    protected string $year;

    public function __construct(ExamRepo $exam, AssessmentRepo $assessment, MyClassRepo $my_class)
    {
        $this->permissionMap = config('permissions.names.tenant.exams');
        parent::__construct();
        $this->exam = $exam;
        $this->assessment = $assessment;
        $this->my_class = $my_class;
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
    public function edit($id): View
    {
        $data['ex'] = $this->exam->findExam($id, false, ['category', 'class_type']);
        $data['exams_cat'] = $this->exam->getCategories();

        return view('pages.exams.edit', $data);
    }

    /**
     * Update the specified exam.
     */
    public function update(ExamUpdate $req, $id): JsonResponse
    {
        $exam = $this->exam->getExamsByConditions(['id' => $id], true, ['assessment'])->first();

        if ($exam->is_locked) {
            return Mk::jsonUpdateDenied();
        }

        $data = $req->only($this->getExamDataKeys($req));
        $this->exam->update($id, $data);

        $classes = $this->my_class->getClassesByConditions(['class_type_id' => $exam->class_type_id]);
        $this->validateAndUpdateNumberFormats($req, $classes, $exam->id);
        $this->handleAssessmentForExam($req, $exam);

        return Mk::jsonUpdateOk();
    }

    /**
     * Remove the specified exam.
     */
    public function destroy($id): RedirectResponse
    {
        $exam = $this->exam->findExam($id);
        if ($exam->is_locked) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $this->exam->delete($id);
        return back()->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Update the locked state of an exam.
     */
    public function update_lock_state(ExamUpdateLockState $req): JsonResponse
    {
        $exam = $this->exam->findExam($req->id);
        if ($exam->is_published) {
            return Mk::jsonUpdateDenied();
        }

        $this->exam->update($req->id, $req->only('is_locked'));
        return Mk::json('ok');
    }

    /**
     * Publish an exam.
     */
    public function publish($exam_id): RedirectResponse
    {
        $this->exam->update($exam_id, ['is_published' => true, 'is_locked' => true]);
        $exam = $this->exam->findExam($exam_id);

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

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Get exam data keys based on whether assessment is enabled.
     */
    protected function getExamDataKeys($req): array
    {
        $has_assessment = $req->boolean('has_assessment');
        return $has_assessment
            ? Mk::getExamFieldNames()
            : Mk::getExamFieldNames(['ca_student_position_by_value', 'cw_denominator', 'hw_denominator', 'tt_denominator', 'tdt_denominator']);
    }

    /**
     * Validate and save number formats for each class during exam creation.
     */
    protected function validateAndSaveNumberFormats($req, $classes, int $exam_id): void
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
    protected function validateAndUpdateNumberFormats($req, $classes, int $exam_id): void
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
    protected function validateNumberFormat($req, $class): void
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
    protected function handleAssessmentForExam($req, $exam): void
    {
        if ($req->boolean('has_assessment')) {
            $this->createAssessmentForExam($exam->id);
        } elseif ($exam->assessment !== null) {
            $this->assessment->delete($exam->assessment->id);
        }
    }
}
