<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Models\Mark;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PromotionController extends Controller
{
    protected string $year;

    public function __construct(protected MyClassRepo $my_class, protected StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.promotions');
        parent::__construct();
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Show promotion form (selector) and optionally student list for a class/section.
     */
    public function promotion($status = null, $fc = null, $fs = null, $tc = null, $ts = null): View|RedirectResponse
    {
        $old_year = $this->year;
        $old_yr = explode('-', $old_year);
        $new_year = ++$old_yr[0].'-'.++$old_yr[1];

        $data = [
            'old_year' => $old_year,
            'new_year' => $new_year,
            'sections' => $this->my_class->getAllSections(),
            'selected' => false,
        ];

        if ($fc && $fs && $tc && $ts) {
            $data['selected'] = true;
            $data['status'] = $status;
            $data['fc'] = $fc;
            $data['fs'] = $fs;
            $data['tc'] = $tc;
            $data['ts'] = $ts;

            $query = ($status == 1)
                ? $this->student->getActiveStudentsQueryWithRelations([
                    'my_class_id' => $fc,
                    'section_id' => $fs,
                    'session' => $old_year,
                ])
                : $this->student->getGraduatedStudentsQuery([
                    'my_class_id' => $fc,
                    'section_id' => $fs,
                    'session' => $old_year,
                ]);

            $students = $query->with('promotion')->get()->whereNotNull('user');

            if ($students->isEmpty()) {
                return redirect()->route('students.promotion')
                    ->with('flash_success', __('msg.nstp'));
            }

            $data['students'] = $students;
        }

        return view('pages.students.promotion.index', $data);
    }

    /**
     * Handle the promotion selection form and redirect to the promotion action.
     */
    public function selector(Request $req): RedirectResponse
    {
        return redirect()->route('students.promotion', [
            $req->status,
            $req->fc,
            $req->fs,
            $req->tc,
            $req->ts,
        ]);
    }

    /**
     * Promote, retain or graduate selected students.
     */
    public function promote(Request $req, int $status, int $fc, int $fs, int $tc, int $ts): RedirectResponse
    {
        $old_year = Qs::getSetting('current_session');
        $old_yr = explode('-', $old_year);
        $new_year = ++$old_yr[0].'-'.++$old_yr[1];

        $students = ($status == 1)
            ? $this->student->getActiveStudentsQueryWithRelations([
                'my_class_id' => $fc,
                'section_id' => $fs,
                'session' => $old_year,
            ])->get()->whereNotNull('user')
            : $this->student->getGraduatedStudentsQuery([
                'my_class_id' => $fc,
                'section_id' => $fs,
                'session' => $old_year,
            ])->get()->whereNotNull('user');

        if ($students->isEmpty()) {
            return redirect()->route('students.promotion')
                ->with('flash_danger', __('msg.srnf'));
        }

        foreach ($students as $st) {
            $action = $req->input("p-{$st->id}");

            $student_update = [];
            if ($action === 'P') { // Promote
                $student_update = [
                    'my_class_id' => $tc,
                    'section_id' => $ts,
                    'is_graduated' => false,
                    'graduated_session' => null,
                    'session' => $new_year,
                ];
            } elseif ($action === 'D') { // Don't Promote
                $student_update = [
                    'my_class_id' => $fc,
                    'section_id' => $fs,
                    'is_graduated' => $st->is_graduated ?? false,
                    'graduated_session' => null,
                    'session' => $old_year,
                ];
            } elseif ($action === 'G') { // Graduated
                $student_update = [
                    'my_class_id' => $fc,
                    'section_id' => $fs,
                    'is_graduated' => true,
                    'graduated_session' => $old_year,
                ];
            }

            if ($student_update) {
                $this->student->updateStudentRecords($st->id, $student_update);
            }

            // Insert promotion record
            $promotion = [
                'previous_my_class_id' => $fc,
                'previous_section_id' => $fs,
                'is_graduated' => ($action === 'G'),
                'new_my_class_id' => \in_array($action, ['D', 'G']) ? $fc : $tc,
                'new_section_id' => \in_array($action, ['D', 'G']) ? $fs : $ts,
                'student_id' => $st->id,
                'user_id' => $st->user_id,
                'user' => $st->user_id,
                'from_session' => $old_year,
                'to_session' => $new_year,
                'status' => $action,
            ];
            $this->student->createPromotion($promotion);
        }

        return redirect()->route('students.promotion')
            ->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Update remarks for a promotion record.
     */
    public function remarks(Request $req, int $p_id): JsonResponse
    {
        Validator::make($req->toArray(), [
            'remarks' => 'sometimes|nullable|string|max:500',
        ])->validate();

        $this->student->updatePromotion(['id' => $p_id], $req->only('remarks'));

        return Qs::jsonUpdateOk();
    }

    /**
     * Show the promotion management / reset page.
     */
    public function manage(): View
    {
        $data['promotions'] = $this->student->getAllPromotions()->whereNotNull('user');
        $data['old_year'] = Qs::getCurrentSession();
        $data['new_year'] = Qs::getNextSession();

        return view('pages.students.promotion.reset', $data);
    }

    /**
     * Reset a single promotion.
     */
    public function reset(int $promotion_id): RedirectResponse
    {
        $this->reset_single($promotion_id);

        return redirect()->route('students.promotion.manage')
            ->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Reset all promotions for the current session → next session.
     */
    public function reset_all(): RedirectResponse
    {
        $where = ['from_session' => Qs::getCurrentSession(), 'to_session' => Qs::getNextSession()];
        $promotions = $this->student->getPromotionsByConditions($where);

        if ($promotions->isNotEmpty()) {
            foreach ($promotions as $prom) {
                $this->reset_single($prom->id);
            }
        }

        return redirect()->route('students.promotion.manage')
            ->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Delete marks for a student in a given year.
     */
    protected function delete_old_marks(int $student_id, string $year): void
    {
        Mark::where(['student_id' => $student_id, 'year' => $year])->delete();
    }

    /**
     * Reset a single promotion record (rollback).
     */
    protected function reset_single(int $promotion_id): void
    {
        $prom = $this->student->findPromotion($promotion_id);

        if (! $prom) {
            return;
        }

        $this->student->updateStudentRecords($prom->user_id, [
            'my_class_id' => $prom->previous_my_class_id,
            'section_id' => $prom->previous_section_id,
            'session' => $prom->from_session,
            'is_graduated' => false,
            'graduated_session' => null,
        ], 'user_id');

        // Delete marks that might have been entered for the new session
        $this->delete_old_marks($prom->student_id, Qs::getNextSession());

        $this->student->deletePromotion($promotion_id);
    }
}
