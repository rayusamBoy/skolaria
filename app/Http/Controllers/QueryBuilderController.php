<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Helpers\Usr;
use App\Http\Requests\QueryBuilderRequest;
use App\Repositories\PermissionRepo;
use App\Repositories\UserRepo;
use App\User;
use Dompdf\Dompdf;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use PermissionKit\Concerns\AuthorizesPermissions;

class QueryBuilderController extends Controller
{
    use AuthorizesPermissions;

    public function __construct(protected UserRepo $user, protected PermissionRepo $permission) {}

    /**
     * Show the query builder index page.
     */
    public function index(): View
    {
        $data['queryed'] = false;
        $data['user_roles'] = $this->user->getRolesByName();

        return view('pages.query_builder.index', $data);
    }

    /**
     * Generate and execute a query based on the request parameters.
     */
    public function select(QueryBuilderRequest $req): View|RedirectResponse
    {
        $data['user_roles'] = $this->user->getRolesByName();

        if ($req->type !== 'select') {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $from = $req->from;
        $select_var = $req->select;

        // Sanitize inputs
        $req['input'] = strip_tags(trim($req->input));
        $req['input_two'] = strip_tags(trim($req->input_two));

        $data['select'] = $select = Qs::getSelectedTableColumns($from, $select_var);
        $data['placeholder'] = Qs::getPresentableTableColumns($from, $select_var);

        if (empty($select)) {
            return back()->with('flash_danger', __('msg.denied'));
        }

        $expression = DB::raw($select);
        $result = $this->getData($req, $expression);

        if (isset($result['msg'])) {
            return back()->with('pop_warning', $result['msg']);
        }

        $data['queryed'] = true;
        $data['records'] = $result['records'];
        $data['query'] = str_replace(
            'placeholder',
            implode(', ', str_replace('_', ' ', Qs::getPresentableTableColumns($from, $select_var))),
            str_replace(['_', '.'], ' ', $result['query2'])
        );
        $data['count'] = $result['records']->count();

        return view('pages.query_builder.index', $data);
    }

    /**
     * Build and execute the actual query based on the extracted parameters.
     *
     * @return array keys: 'records' (collection), 'query2' (string) or 'msg' (error string)
     */
    private function getData(QueryBuilderRequest $req, Expression $expression): array
    {
        $from = $req->from;
        $where = $req->where;
        $where_two = $req->where_two == 'none' ? null : $req->where_two;
        $condition = $req->condition;
        $condition_two = $req->condition_two;
        $input = $req->input;
        $input_two = $req->input_two;
        $limit = $req->limit;

        $orderby_column = explode(' ', $req->orderby_column)[0];
        if ($orderby_column == 'id') {
            $orderby_column = "$from.$orderby_column";
        }
        $orderby_direction = explode(' ', $req->orderby_direction)[0];

        // LIKE handling
        if ($condition === 'like') {
            $input = "%$input%";
        }
        if ($condition_two === 'like') {
            $input_two = "%$input_two%";
        }

        // Prepare where clause parts
        $has_first_where = $where !== 'none' && $input !== null;
        $has_second_where = $where_two !== null && $input_two !== null;

        // Build query string for display
        $display_query = $this->buildDisplayQuery($from, $where, $where_two, $condition, $condition_two, $has_first_where, $has_second_where, $orderby_column, $orderby_direction, $limit);

        if (! $has_first_where) {
            if ($has_second_where) {
                return ['msg' => 'The first where clause is empty. You must use it first before using the second where clause.'];
            }
            // No where conditions
            $records = $this->buildBaseQuery($from, $expression, $orderby_column, $orderby_direction, $limit);

            return ['records' => $records, 'query2' => $display_query];
        }

        // First where is given – build where clause(s)
        $where_column = explode(' ', $where)[0];
        $bindings = [$input];
        $wheres = [[$where_column, $condition, '?']];

        if ($has_second_where) {
            $where_two_column = explode(' ', $where_two)[0];
            $wheres[] = [$where_two_column, $condition_two, '?'];
            $bindings[] = $input_two;
        }

        $records = $this->buildBaseQuery($from, $expression, $orderby_column, $orderby_direction, $limit, $wheres, $bindings);

        return ['records' => $records, 'query2' => $display_query];
    }

    /**
     * Build the base query (shared between with/without where clauses).
     *
     * @param  array  $wheres  array of [column, operator, value] (value placeholder '?' recommended)
     * @param  array  $bindings  values to bind in order
     */
    private function buildBaseQuery(string $from, Expression $expression, string $orderby_column, string $orderby_direction, ?int $limit, array $wheres = [], array $bindings = []): Collection
    {
        // Special tables that require joins
        $special_tables = ['student_records', 'staff_records', 'exam_records', 'grades', 'subjects'];
        if (! \in_array($from, $special_tables)) {
            $query = DB::table($from)->select($expression);
            $this->applyWheresAndOrderLimit($query, $wheres, $bindings, $orderby_column, $orderby_direction, $limit);

            return $query->get();
        }

        // Hand off to specialized builders
        return match ($from) {
            'student_records' => $this->buildStudentRecordsQuery($expression, $orderby_column, $orderby_direction, $limit, $wheres, $bindings),
            'staff_records' => $this->buildStaffRecordsQuery($expression, $orderby_column, $orderby_direction, $limit, $wheres, $bindings),
            'exam_records' => $this->buildExamRecordsQuery($expression, $orderby_column, $orderby_direction, $limit, $wheres, $bindings),
            'grades' => $this->buildGradesQuery($expression, $orderby_column, $orderby_direction, $limit, $wheres, $bindings),
            'subjects' => $this->buildSubjectsQuery($expression, $orderby_column, $orderby_direction, $limit, $wheres, $bindings),
        };
    }

    /**
     * Apply where conditions, ordering, and limit to a generic query builder.
     */
    private function applyWheresAndOrderLimit(mixed $query, array $wheres, array $bindings, string $orderby_column, string $orderby_direction, ?int $limit): void
    {
        foreach ($wheres as $index => $w) {
            $query->where($w[0], $w[1], $w[2]);
        }
        if ($bindings) {
            $query->setBindings($bindings);
        }
        $query->orderBy($orderby_column, $orderby_direction);
        if ($limit) {
            $query->limit($limit);
        }
    }

    /**
     * Build student records query with all needed joins.
     */
    private function buildStudentRecordsQuery(Expression $expression, string $orderby_column, string $orderby_direction, ?int $limit, array $wheres, array $bindings): Collection
    {
        $query = DB::table('student_records')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->leftJoin('users as parents', 'parents.id', '=', 'student_records.my_parent_id')
            ->join('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->join('sections', 'sections.id', '=', 'student_records.section_id')
            ->join('dorms', 'dorms.id', '=', 'student_records.dorm_id')
            ->whereNull('users.deleted_at')
            ->select($expression)
            ->distinct();

        $this->applyWheresAndOrderLimit($query, $wheres, $bindings, $orderby_column, $orderby_direction, $limit);

        return $query->get();
    }

    /**
     * Build staff records query.
     */
    private function buildStaffRecordsQuery(Expression $expression, string $orderby_column, string $orderby_direction, ?int $limit, array $wheres, array $bindings): Collection
    {
        $query = DB::table('staff_records')
            ->join('users', 'users.id', '=', 'staff_records.user_id')
            ->select($expression)
            ->distinct();

        $this->applyWheresAndOrderLimit($query, $wheres, $bindings, $orderby_column, $orderby_direction, $limit);

        return $query->get();
    }

    /**
     * Build exam records query.
     */
    private function buildExamRecordsQuery(Expression $expression, string $orderby_column, string $orderby_direction, ?int $limit, array $wheres, array $bindings): Collection
    {
        $query = DB::table('exam_records')
            ->join('users', 'users.id', '=', 'exam_records.student_id')
            ->join('my_classes', 'my_classes.id', '=', 'exam_records.my_class_id')
            ->join('exams', 'exams.id', '=', 'exam_records.exam_id')
            ->join('sections', 'sections.id', '=', 'exam_records.section_id')
            ->join('divisions', 'divisions.id', '=', 'exam_records.division_id')
            ->join('grades', 'grades.id', '=', 'exam_records.grade_id')
            ->select($expression)
            ->distinct();

        $this->applyWheresAndOrderLimit($query, $wheres, $bindings, $orderby_column, $orderby_direction, $limit);

        return $query->get();
    }

    /**
     * Build grades query.
     */
    private function buildGradesQuery(Expression $expression, string $orderby_column, string $orderby_direction, ?int $limit, array $wheres, array $bindings): Collection
    {
        $query = DB::table('grades')
            ->join('class_types', 'class_types.id', '=', 'grades.class_type_id')
            ->select($expression)
            ->distinct();

        $this->applyWheresAndOrderLimit($query, $wheres, $bindings, $orderby_column, $orderby_direction, $limit);

        return $query->get();
    }

    /**
     * Build subjects query.
     */
    private function buildSubjectsQuery(Expression $expression, string $orderby_column, string $orderby_direction, ?int $limit, array $wheres, array $bindings): Collection
    {
        $query = DB::table('subjects')
            ->join('my_classes', 'my_classes.id', '=', 'subjects.my_class_id')
            ->select($expression)
            ->distinct();

        $this->applyWheresAndOrderLimit($query, $wheres, $bindings, $orderby_column, $orderby_direction, $limit);

        return $query->get();
    }

    /**
     * Build the human‑readable query string for display.
     */
    private function buildDisplayQuery(string $from, string $where, string $where_two, string $condition, string $condition_two, bool $has_first, bool $has_second, string $orderby_column, string $orderby_direction, ?int $limit): string
    {
        $base = "select distinct placeholder from $from";

        if ($has_first) {
            $where_column = explode(' ', $where)[0];
            $base .= " where $where_column $condition '?'";
        }
        if ($has_second && $has_first) {
            $where_two_column = explode(' ', $where_two)[0];
            $base .= " and $where_two_column $condition_two '?'";
        }

        $orderby_direction_extended = "{$orderby_direction}ending";
        $limit_str = $limit ? " limit $limit" : '';

        if (! $has_first && ! $has_second) {
            // no where
            $base .= " orderby $orderby_column $orderby_direction_extended $limit_str";
        } else {
            // ordering is part of the base but display may differ; we just show the order info
            $base .= " (ordered by $orderby_column $orderby_direction_extended $limit_str)";
        }

        return $base;
    }

    /**
     * Generate a PDF of users data filtered by role.
     */
    public function print_users_data(HttpRequest $req)
    {
        Validator::make($req->toArray(), [
            'user_roles' => 'required|array',
            'per_page' => 'integer|min:3|max:15|required',
            'paper_size' => 'required|string|in:'.implode(',', array_keys(Qs::getDompdfSupportedPaperSizes())),
            'paper_orientation' => 'sometimes|nullable|in:landscape',
        ], [], ['per_page' => 'number of data per page'])->validate();

        $users = DB::table('users')
            ->leftJoin('staff_records', 'users.id', '=', 'staff_records.user_id')
            ->select([
                'users.photo_path',
                'users.name',
                'staff_records.designation',
                'staff_records.place_of_living',
                'users.date_of_birth',
                'users.primary_mobile',
                'users.email',
                'users.primary_identification_number',
                'users.secondary_identification_number',
                'staff_records.employment_date',
                'staff_records.employment_number',
                'staff_records.confirmation_date',
                'staff_records.licence_number',
                'staff_records.bank_account_number',
                'staff_records.bank_name',
                'staff_records.file_number',
                'staff_records.tin_number',
                'staff_records.social_security_number',
                'staff_records.education_level',
                'staff_records.college_attended',
                'staff_records.year_graduated',
                'staff_records.subjects_studied',
                'staff_records.number_of_periods',
            ])
            ->whereIn('users.id', function ($query) use ($req) {
                $query->select('model_id')
                    ->from('model_has_roles')
                    ->whereIn('role_id', function ($subquery) use ($req) {
                        $subquery->select('id')
                            ->from('roles')
                            ->whereIn('name', $req->user_roles);
                    })
                    ->where('model_type', User::class);
            })
            ->orderBy('users.name')
            ->get();

        $users = $users->map(function ($user) {
            $user->photo_path = Qs::imgToBase64($user->photo_path);

            return $user;
        });

        // Build paginated HTML
        $keys = [];
        foreach ($users as $teacher) {
            foreach ($teacher as $key => $value) {
                $keys[] = $key;
            }
        }

        $logo = Qs::imgToBase64(Usr::getTenancyAwareImgOrDefaultBgUrl(Qs::getSettings()->firstWhere('key', 'logo')));
        $unique_keys = array_unique($keys);
        $users_arr = $users->toArray();
        $total = \count($users_arr);
        $per_page = $req->per_page;
        $last_page = max((int) ceil($total / $per_page), 1);

        $data_str = '';
        for ($i = 1; $i <= $last_page; $i++) {
            $start = ($i - 1) * $per_page;
            $slice = \array_slice($users_arr, $start, $per_page, true);

            $data_str .= view('pages.query_builder.print.staff_data.body', [
                'staff' => $slice,
                'logo' => $logo,
                'keys' => $unique_keys,
                'page_number' => $i,
                'last_page' => $last_page,
            ])->render();
        }

        $dompdf_html = view('pages.query_builder.print.staff_data.index', [
            'staff' => $data_str,
        ])->render();

        $paper_size = $req->paper_size ?? 'a3';
        $paper_orientation = $req->paper_orientation ?? 'portrait';

        $dompdf = new Dompdf([
            'defaultPaperSize' => 'A3',
            'chroot' => public_path(),
        ]);
        $dompdf->loadHtml($dompdf_html);
        $dompdf->setPaper($paper_size, $paper_orientation);
        $dompdf->render();

        return $dompdf->stream(Qs::getSystemShortCode().' Users Data.pdf');
    }
}
