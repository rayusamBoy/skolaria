<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\Usr;
use App\Repositories\AssessmentRepo;
use App\Repositories\BookRepo;
use App\Repositories\ExamRepo;
use App\Repositories\MarkRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\NoticeRepo;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\User;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\View;

class HomeController extends Controller
{
    protected $user, $class, $section, $exam, $payment, $notice, $student, $mark, $year, $assessment, $book;
    public function __construct(UserRepo $user, MyClassRepo $class, MyClassRepo $section, BookRepo $book, ExamRepo $exam, PaymentRepo $payment, NoticeRepo $notice, StudentRepo $student, MarkRepo $mark, AssessmentRepo $assessment)
    {
        $this->user = $user;
        $this->class = $class;
        $this->section = $section;
        $this->exam = $exam;
        $this->payment = $payment;
        $this->notice = $notice;
        $this->student = $student;
        $this->mark = $mark;
        $this->assessment = $assessment;
        $this->book = $book;
        $this->year = Usr::getSetting('current_session');
    }

    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Usr::getSetting('phone', true);

        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Usr::getSetting('phone', true);

        return view('pages.other.terms_of_use', $data);
    }

    /**
     * Show dashboard.
     *
     * @param Request $request
     * @return View|ViewContract
     */
    public function dashboard(Request $request): View|ViewContract
    {
        // Notices
        [$unviewed_count, $unviewed_notices, $viewed_notices] = $this->splitNoticesByViewed();

        $un_paginated = $this->getPaginator($request, $unviewed_notices, "unviewed-notices-page");
        $vn_paginated = $this->getPaginator($request, $viewed_notices, "viewed-notices-page");

        $d = [
            'unviewed_count' => $unviewed_count,
        ];

        if ($request->ajax()) {
            if (str_contains($request->fullUrl(), 'unviewed')) {
                $d['unviewed_notices'] = $un_paginated;
            } else {
                $d['viewed_notices'] = $vn_paginated;
            }

            $d['current_url'] = $request->url();

            return view('pages/notices/show', $d);
        }

        // Base lists / collections
        $student_recs = $this->student->all()->whereNotNull('user');
        $books_requests = $this->book->allRequests();
        $users = $this->user->all();

        // Staff / user breakdown
        $staff_breakdown = $this->buildStaffBreakdown();

        $parents = $this->user->getUsersByRoleNames(['Parent']);
        $active_students = $student_recs->where('grad', 0);
        $grad_students = $student_recs->where('grad', 1);

        // Chart data (last N months)
        $months_count = 12;
        ['months' => $months, 'periods' => $periods] = $this->buildMonths($months_count);

        $students_by_month = $this->countByPeriods($periods, $student_recs, fn($s) => $s->user->created_at);

        $payments_collection = $this->payment->allCleanPaidRecs();
        $payments_by_month = $this->countByPeriods($periods, $payments_collection, fn($p) => $p->created_at);

        // Gender counts (students)
        $male_students = $student_recs->where('user.gender', User::GENDER_MALE)->count();
        $female_students = $student_recs->where('user.gender', User::GENDER_FEMALE)->count();

        // Students per class
        $class_labels = [];
        $class_counts = [];
        foreach (Usr::getAllClassesOrdered() as $c) {
            $class_labels[] = $c->name;
            $class_counts[] = $student_recs->where('my_class_id', $c->id)->count();
        }

        // Put together $d (view data)
        $d = [...$d, 'unviewed_notices' => $un_paginated,
            'viewed_notices' => $vn_paginated,

            'subjects' => $this->class->getUniqueSubjectsNames(),
            'classes_count' => $this->class->all()->count(),
            'section' => $this->section->getAllSections(),
            'exams_count' => $this->exam->getPublishedExamsCount(),
            'assessments_count' => $this->assessment->get()->whereNotNull('exam')->count(),
            'total_pay_records' => $payments_collection->count(),
            'books_count' => $this->book->countAll(),
            'book_requests_count' => $books_requests->count(),
            'book_requests_returned_count' => $books_requests->where('returned', 1)->count(),

            // Charts
            'chart_months' => $months,
            'students_monthly' => $students_by_month,
            'payments_monthly' => $payments_by_month,
            'gender_counts' => ['male' => $male_students, 'female' => $female_students],

            // Staff & counts
            'staff_breakdown' => $staff_breakdown,
            'parents_count' => $parents->count(),
            'parents_male_count' => $parents->where('gender', User::GENDER_MALE)->count(),
            'parents_female_count' => $parents->where('gender', User::GENDER_FEMALE)->count(),
            'students_count' => $student_recs->count(),
            'active_students_count' => $active_students->count(),
            'active_students_male_count' => $active_students->where('user.gender', User::GENDER_MALE)->count(),
            'active_students_female_count' => $active_students->where('user.gender', User::GENDER_FEMALE)->count(),
            'grad_students_count' => $grad_students->count(),
            'grad_students_male_count' => $grad_students->where('user.gender', User::GENDER_MALE)->count(),
            'grad_students_female_count' => $grad_students->where('user.gender', User::GENDER_FEMALE)->count(),
            'students_per_class_labels' => $class_labels,
            'students_per_class_counts' => $class_counts];

        $view = Usr::tenancyInitilized() ? 'pages.dashboard' : 'pages.central.dashboard';
        return view($view, $d);
    }

    /**
     * Split notices into unviewed and viewed for current auth user.
     *
     * @return array [int $unviewed_count, Collection $unviewed_notices, Collection $viewed_notices]
     */
    private function splitNoticesByViewed(): array
    {
        $unviewed_notices = collect();
        $viewed_notices = collect();
        $unviewed_count = 0;

        $notices = $this->notice->allExceptAuth()->sortBy("id", SORT_REGULAR, true);

        $auth_id = auth()->id();

        foreach ($notices as $ntc) {
            $v_ids = $ntc->viewers_ids;
            $viewers = $v_ids === null ? [] : json_decode($v_ids, true);
            if ($v_ids === null || !\is_array($viewers) || !\in_array($auth_id, $viewers, true)) {
                $unviewed_count++;
                $unviewed_notices->push($ntc);
            } else {
                $viewed_notices->push($ntc);
            }
        }

        return [$unviewed_count, $unviewed_notices, $viewed_notices];
    }

    /**
     * Build last N months labels and periods.
     *
     * @param int $months_count
     * @return array{months: array, periods: array}
     */
    private function buildMonths(int $months_count): array
    {
        $months = [];
        $periods = [];
        $now = Carbon::now();

        for ($i = $months_count - 1; $i >= 0; $i--) {
            $m = $now->copy()->subMonths($i);
            $months[] = $m->format('M Y');
            $periods[] = $m->format('Y-m');
        }

        return ['months' => $months, 'periods' => $periods];
    }

    /**
     * Count items in a collection by period (YYYY-mm) using a date accessor.
     *
     * @param array $periods  array of period keys like ['2024-09', ...]
     * @param Collection $collection
     * @param callable $dateAccessor function($item): \DateTimeInterface|null|string|null
     * @return array numeric array with counts aligned to $periods
     */
    private function countByPeriods(array $periods, Collection $collection, callable $dateAccessor): array
    {
        return collect($periods)
            ->mapWithKeys(function ($p) use ($collection, $dateAccessor) {
                $count = $collection->filter(function ($item) use ($p, $dateAccessor) {
                    $d = $dateAccessor($item);
                    if ($d === null)
                        return false;
                    // If $d is a Carbon/DateTime instance:
                    if ($d instanceof \DateTimeInterface) {
                        return $d->format('Y-m') === $p;
                    }
                    // If it's a string, attempt parse via Carbon
                    try {
                        $dt = Carbon::parse($d);
                        return $dt->format('Y-m') === $p;
                    } catch (\Throwable $e) {
                        return false;
                    }
                })->count();

                return [$p => $count];
            })->values()->toArray();
    }

    /**
     * Build staff breakdown counts from $users collection.
     *
     * @param Collection $users
     * @return array
     */
    private function buildStaffBreakdown(): array
    {
        $admins = $this->user->getUsersByRoleNames(['Super Admin', 'Admin']);
        return [
            'admins_count' => $admins->count(),
            'admins_male_count' => $admins->where('gender', User::GENDER_MALE)->count(),
            'admins_female_count' => $admins->where('gender', User::GENDER_FEMALE)->count(),
            'teachers' => $this->user->getUsersByRoleNames(['Teacher'])->count(),
            'companions' => $this->user->getUsersByRoleNames(['Companion'])->count(),
            'accountants' => $this->user->getUsersByRoleNames(['Accountant'])->count(),
            'librarians' => $this->user->getUsersByRoleNames(['Librarian'])->count(),
        ];
    }

    private function getPaginator(Request $request, Collection $items, string $page_name): LengthAwarePaginator
    {
        $total = $items->count();
        $perPage = 4;
        $currentPage = (int) ($request->input($page_name) ?? 1);
        $currentPage = $currentPage > 0 ? $currentPage : 1;

        // slice() keeps original keys — values() reindexes the slice which is usually preferred for front-end lists
        $pagedItems = $items->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $pagedItems,
            $total,
            $perPage,
            $currentPage,
            [
                'pageName' => $page_name,
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}
