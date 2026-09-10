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
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected UserRepo $user,
        protected MyClassRepo $class,
        protected MyClassRepo $section,
        protected BookRepo $book,
        protected ExamRepo $exam,
        protected PaymentRepo $payment,
        protected NoticeRepo $notice,
        protected StudentRepo $student,
        protected MarkRepo $mark,
        protected AssessmentRepo $assessment
    ) {}

    /**
     * Show privacy policy page.
     */
    public function privacy_policy(): View
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Usr::getSetting('phone', true);

        return view('pages.other.privacy_policy', $data);
    }

    /**
     * Show terms of use page.
     */
    public function terms_of_use(): View
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Usr::getSetting('phone', true);

        return view('pages.other.terms_of_use', $data);
    }

    /**
     * Show dashboard with statistics and notices.
     */
    public function dashboard(Request $request): View|ViewContract
    {
        [$unviewed_count, $unviewed_notices, $viewed_notices] = $this->splitNoticesByViewed();

        $un_paginated = $this->getPaginator($request, $unviewed_notices, 'unviewed-notices-page');
        $vn_paginated = $this->getPaginator($request, $viewed_notices, 'viewed-notices-page');

        $data = ['unviewed_count' => $unviewed_count];

        if ($request->ajax()) {
            $data[str_contains($request->fullUrl(), 'unviewed') ? 'unviewed_notices' : 'viewed_notices'] =
                str_contains($request->fullUrl(), 'unviewed') ? $un_paginated : $vn_paginated;
            $data['current_url'] = $request->url();

            return view('pages.notices.show', $data);
        }

        $student_recs = $this->student->getStudentRecords([], ['section', 'user'])->whereNotNull('user');
        $books_requests = $this->book->getAllBookRequests();
        $parents = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.parent')]);

        $active_students = $student_recs->where('is_graduated', false);
        $grad_students = $student_recs->where('is_graduated', true);

        $months_count = 12;
        ['months' => $months, 'periods' => $periods] = $this->buildMonths($months_count);

        $students_by_month = $this->countByPeriods($periods, $student_recs, fn ($s) => $s->user->created_at);
        $payments_collection = $this->payment->getPaidRecordsWithValidPayment();
        $payments_by_month = $this->countByPeriods($periods, $payments_collection, fn ($p) => $p->created_at);

        $gender_counts = $this->getGenderCounts($student_recs, 'user.gender');

        $class_labels = [];
        $class_counts = [];
        foreach (Usr::getAllClassesOrdered() as $class) {
            $class_labels[] = $class->name;
            $class_counts[] = $student_recs->where('my_class_id', $class->id)->count();
        }

        $data = [
            ...$data,
            'unviewed_notices' => $un_paginated,
            'viewed_notices' => $vn_paginated,
            'subjects' => $this->class->getUniqueSubjectsNames(),
            'section' => $this->section->getAllSections(),
            'exams_count' => $this->exam->countPublishedExams(),
            'assessments_count' => $this->assessment->getAssessmentsWithValidExam([])->count(),
            'total_pay_records' => $payments_collection->count(),
            'books_count' => $this->book->countBooks(),
            'book_requests_count' => $books_requests->count(),
            'book_requests_returned_count' => $books_requests->where('is_returned', true)->count(),
            'chart_months' => $months,
            'students_monthly' => $students_by_month,
            'payments_monthly' => $payments_by_month,
            'gender_counts' => $gender_counts,
            'staff_breakdown' => $this->buildStaffBreakdown(),
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
            'students_per_class_counts' => $class_counts,
        ];

        $view = Usr::tenancyInitialized() ? 'pages.dashboard' : 'pages.central.dashboard';

        return view($view, $data);
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Split notices into unviewed and viewed for the authenticated user.
     */
    protected function splitNoticesByViewed(): array
    {
        $notices = $this->notice->getAllExceptOwn()->sortByDesc('id');
        $auth_id = auth()->id();

        [$unviewed, $viewed] = $notices->partition(function ($notice) use ($auth_id) {
            $viewers = $notice->viewers_ids;

            return $viewers === null || ! \is_array($viewers) || ! \in_array($auth_id, $viewers, true);
        });

        return [$unviewed->count(), $unviewed, $viewed];
    }

    /**
     * Build last N months labels and periods.
     */
    protected function buildMonths(int $months_count): array
    {
        $months = [];
        $periods = [];
        $now = Carbon::now();

        for ($i = $months_count - 1; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $months[] = $month->format('M Y');
            $periods[] = $month->format('Y-m');
        }

        return ['months' => $months, 'periods' => $periods];
    }

    /**
     * Count items in a collection by period (YYYY-mm) using a date accessor.
     */
    protected function countByPeriods(array $periods, Collection $collection, callable $dateAccessor): array
    {
        return collect($periods)
            ->mapWithKeys(function ($period) use ($collection, $dateAccessor) {
                $count = $collection->filter(function ($item) use ($period, $dateAccessor) {
                    $date = $dateAccessor($item);
                    if ($date === null) {
                        return false;
                    }
                    if ($date instanceof \DateTimeInterface) {
                        return $date->format('Y-m') === $period;
                    }
                    try {
                        return Carbon::parse($date)->format('Y-m') === $period;
                    } catch (\Throwable $e) {
                        return false;
                    }
                })->count();

                return [$period => $count];
            })
            ->values()
            ->toArray();
    }

    /**
     * Build staff breakdown counts.
     */
    protected function buildStaffBreakdown(): array
    {
        $admins = $this->user->getUsersByRoleNames([
            config('roles.default_roles.tenant.superadmin'),
            config('roles.default_roles.tenant.admin'),
        ]);

        return [
            'admins_count' => $admins->count(),
            'admins_male_count' => $admins->where('gender', User::GENDER_MALE)->count(),
            'admins_female_count' => $admins->where('gender', User::GENDER_FEMALE)->count(),
            'teachers' => $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.teacher')])->count(),
            'accountants' => $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.accountant')])->count(),
            'librarians' => $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.librarian')])->count(),
        ];
    }

    /**
     * Get gender counts (male/female) from a collection using a nested attribute.
     */
    protected function getGenderCounts(Collection $collection, string $genderPath): array
    {
        $male = 0;
        $female = 0;
        foreach ($collection as $item) {
            $gender = data_get($item, $genderPath);
            if ($gender === User::GENDER_MALE) {
                $male++;
            } elseif ($gender === User::GENDER_FEMALE) {
                $female++;
            }
        }

        return ['male' => $male, 'female' => $female];
    }

    /**
     * Create a paginator for a collection.
     */
    protected function getPaginator(Request $request, Collection $items, string $page_name): LengthAwarePaginator
    {
        $total = $items->count();
        $perPage = 4;
        $currentPage = max(1, (int) ($request->input($page_name) ?? 1));

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
