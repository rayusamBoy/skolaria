<?php

namespace App\Helpers;

use App\Models\ExamAnnounce;
use App\Models\ParentRelative;
use App\Models\Section;
use App\Models\Setting;
use App\Models\StudentRecord;
use DateTime;
use Dompdf\Adapter\CPDF;
use Hashids\Hashids;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use NotificationChannels\Fcm\FcmChannel;
use Parsedown;

class Qs
{
    /**
     * Get the application code (short system title).
     */
    public static function getSystemShortCode(): string
    {
        return self::getSetting('system_title') ?? 'SMS';
    }

    /**
     * Return standard panel options HTML (collapse/remove buttons).
     */
    public static function getPanelOptionsHtml(): string
    {
        return '<div class="header-elements print-none">
                    <div class="list-icons">
                        <a class="list-icons-item" data-action="collapse"></a>
                        <a class="list-icons-item" data-action="remove"></a>
                    </div>
                </div>';
    }

    /**
     * Get the parent relative record for a given user ID.
     *
     * @param  mixed  $user_id
     */
    public static function getParentRelativeByUserId($user_id): ?ParentRelative
    {
        return ParentRelative::where('user_id', $user_id)->first();
    }

    /** @var Hashids|null Singleton Hashids instance */
    protected static ?Hashids $hashids = null;

    /**
     * Get or create the Hashids instance (uses tenant key or app key as salt).
     */
    protected static function getHashidsInstance(): Hashids
    {
        if (! self::$hashids) {
            $salt = tenant()?->getTenantKey() ?? config('app.key');
            self::$hashids = new Hashids($salt, 10);
        }

        return self::$hashids;
    }

    /**
     * Hash an ID using Hashids.
     */
    public static function hash(mixed $id): string
    {
        return self::getHashidsInstance()->encode($id);
    }

    /**
     * Decode a hashed string back to the original ID(s).
     *
     * @param  bool  $to_string  Whether to return a comma-separated string or an array
     */
    public static function decodeHash(string $str, bool $to_string = true): string|array
    {
        $decoded = self::getHashidsInstance()->decode($str);

        return $to_string ? implode(',', $decoded) : $decoded;
    }

    /**
     * Get the user record fields (name → column mapping).
     *
     * @param  array  $remove  Keys to exclude
     * @param  bool  $values_only  Return only column names if true
     */
    public static function getUserRecordFields(array $remove = [], bool $values_only = true): array
    {
        $data = [
            'Full name' => 'name',
            'Email' => 'email',
            'Primary mobile' => 'primary_mobile',
            'Secondary mobile' => 'secondary_mobile',
            'Date of birth' => 'date_of_birth',
            'Gender' => 'gender',
            'Address' => 'address',
            'Blood group' => 'blood_group_id',
            'Nationality' => 'nationality_id',
            'State' => 'state_id',
            'LGA' => 'lga_id',
            'Religion' => 'religion',
        ];

        $filtered = $remove ? array_diff($data, $remove) : $data;

        return $values_only ? array_values($filtered) : $filtered;
    }

    /**
     * Get the staff record fields (name → column mapping).
     */
    public static function getStaffRecordFields(array $remove = [], bool $values_only = true): array
    {
        $data = [
            'Employment date' => 'employment_date',
            'Employment number' => 'employment_number',
            'Confirmation date' => 'confirmation_date',
            'Licence number' => 'licence_number',
            'File number' => 'file_number',
            'Bank account number' => 'bank_account_number',
            'TIN number' => 'tin_number',
            'Education level' => 'education_level',
            'Year graduated' => 'year_graduated',
            'Social security number' => 'social_security_number',
            'College attended' => 'college_attended',
            'Role' => 'role',
            'Subjects studied' => 'subjects_studied',
            'Bank name' => 'bank_name',
            'Number of periods' => 'number_of_periods',
            'Place of living' => 'place_of_living',
        ];

        $filtered = $remove ? array_diff($data, $remove) : $data;

        return $values_only ? array_values($filtered) : $filtered;
    }

    /**
     * Get parent/relative record fields (name → column mapping).
     */
    public static function getParentRelativeRecordFields(array $remove = [], bool $values_only = true): array
    {
        $data = [
            'User' => 'user_id',
            'Relative Full name' => 'relative_name',
            'Relative secondary mobile' => 'relative_secondary_mobile',
            'Relative primary mobile' => 'relative_primary_mobile',
            'Relation with relative' => 'relation',
        ];

        $filtered = $remove ? array_diff($data, $remove) : $data;

        return $values_only ? array_values($filtered) : $filtered;
    }

    /**
     * Get student data fields (name → column mapping).
     */
    public static function getStudentDataFields(array $remove = [], bool $values_only = true): array
    {
        $data = [
            'Class' => 'my_class_id',
            'Section' => 'section_id',
            'Admission number' => 'admission_number',
            'Parent' => 'my_parent_id',
            'Dorm' => 'dorm_id',
            'Dorm room number' => 'dorm_room_no',
            'Date admitted' => 'date_admitted',
            'House number' => 'house_number',
            'Age' => 'age',
            'Primary school name' => 'primary_school_name',
            'Secondary school name' => 'secondary_school_name',
            'Birth certificate' => 'birth_certificate',
            'Disability' => 'disability',
            'Chronic health problems' => 'chronic_health_problems',
            'Parents status' => 'parent_status',
            'Food taboos' => 'food_taboos',
        ];

        $filtered = $remove ? array_diff($data, $remove) : $data;

        return $values_only ? array_values($filtered) : $filtered;
    }

    /**
     * Convert a number (1‑based) to its corresponding uppercase letter (1 -> A, 2 -> B, etc.).
     */
    public static function numberToColumnLetter(int $number): string
    {
        return \chr(64 + $number);
    }

    /**
     * Check if the authenticated user is a class teacher for any section.
     */
    public static function userIsClassSectionTeacher(): bool
    {
        return auth()->user()->hasRole(config('roles.default_roles.tenant.teacher'))
            ? Section::where('teacher_id', auth()->id())->exists()
            : false;
    }

    /**
     * Determine if a user (default: auth) is the system head.
     */
    public static function userIsHead(?int $user_id = null): bool
    {
        $user_id ??= auth()->id();

        return \in_array($user_id, self::getHeadIds());
    }

    /**
     * Return an array of user IDs considered as system heads.
     */
    public static function getHeadIds(): array
    {
        return [1];
    }

    /**
     * Get staff role names, optionally excluding some.
     */
    public static function getStaffRoleNames(array $remove = []): array
    {
        $data = array_values(config('roles.staff_roles.tenant'));

        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    /**
     * Get the list of exam data fields, optionally excluding some.
     */
    public static function getExamFieldNames(array $remove = []): array
    {
        $data = [
            'name',
            'term',
            'category_id',
            'number_format',
            'class_type_id',
            'exam_denominator',
            'exam_student_position_by_value',
            'exam_student_position_sort_order',
            'ca_student_position_by_value',
            'ca_student_position_sort_order',
            'cw_denominator',
            'hw_denominator',
            'tt_denominator',
            'tdt_denominator',
        ];

        return $remove ? array_values(array_diff($data, $remove)) : $data;
    }

    /**
     * Check if a given student is a child of a parent.
     */
    public static function isStudentChildOfParent(int $student_id, int $parent_id): bool
    {
        return StudentRecord::where(['user_id' => $student_id, 'my_parent_id' => $parent_id])->exists();
    }

    /**
     * Get the student record by user ID.
     */
    public static function getSRByUserID(int $user_id): ?StudentRecord
    {
        return StudentRecord::where('user_id', $user_id)->first();
    }

    /**
     * Get the public upload path (relative).
     */
    public static function getPublicUploadPath(): string
    {
        return 'uploads/';
    }

    /**
     * Get role‑specific upload path.
     */
    public static function getUploadPath(string $user_role_name): string
    {
        return "uploads/$user_role_name";
    }

    /**
     * Extract file metadata (name, extension, MIME, size).
     */
    public static function getFileMetaData(UploadedFile $file): array
    {
        return [
            'name' => $file->getClientOriginalName(),
            'ext' => $file->getClientOriginalExtension(),
            'type' => $file->getClientMimeType(),
            'size' => self::formatBytes($file->getSize()),
        ];
    }

    /**
     * Return the delimiter used in combined strings (e.g., file names).
     */
    public static function getDelimiter(): string
    {
        return '-';
    }

    /**
     * Format a byte value into a human‑readable size.
     */
    public static function formatBytes(int $size, int $precision = 2): string
    {
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision).' '.$suffixes[floor($base)];
    }

    // ------ Settings helpers ------

    /**
     * Retrieve a setting by type, optionally from the central database.
     */
    public static function getSetting(string $key, bool $use_central_connection = false): mixed
    {
        if ($use_central_connection) {
            return tenancy()->central(fn () => once(fn () => Setting::where('key', $key)->value('value')));
        }

        return once(fn () => Setting::where('key', $key)->value('value'));
    }

    /**
     * Get all settings (cached once per request).
     */
    public static function getSettings(): Collection
    {
        return once(fn () => Setting::all());
    }

    /**
     * Return the current academic session string (e.g., "2023-2024").
     */
    public static function getCurrentSession(): ?string
    {
        return self::getSetting('current_session');
    }

    /**
     * Compute the next academic session based on the current one.
     */
    public static function getNextSession(): string
    {
        $oy = self::getCurrentSession();
        $years = explode('-', $oy);

        return ++$years[0].'-'.++$years[1];
    }

    /**
     * Get the system name.
     */
    public static function getSystemName(): ?string
    {
        return self::getSetting('system_name');
    }

    /**
     * Get the abbreviation (first letter of each word) of a string or the system name.
     */
    public static function getStringAbbreviation(?string $string = null): string
    {
        $string ??= self::getSystemName();
        preg_match_all('/(?<=\b)\w/iu', $string, $matches);

        return mb_strtoupper(implode('', $matches[0]));
    }

    /**
     * Find a student record by user ID.
     */
    public static function findStudentRecord(int $user_id): ?StudentRecord
    {
        return StudentRecord::where('user_id', $user_id)->first();
    }

    // ------ JSON response helpers ------

    /**
     * Return a JSON response (clearing any output buffering).
     *
     * @param  array  $arr  this will take precedence if provided
     */
    public static function json(?string $msg = null, bool $ok = true, array $arr = []): JsonResponse
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return $arr ? response()->json($arr) : response()->json(['ok' => $ok, 'msg' => $msg]);
    }

    /**
     * JSON store success response.
     */
    public static function jsonStoreOk(): JsonResponse
    {
        return self::json(__('msg.store_ok'));
    }

    /**
     * JSON update success response.
     */
    public static function jsonUpdateOk(): JsonResponse
    {
        return self::json(__('msg.update_ok'));
    }

    /**
     * JSON update denied response.
     */
    public static function jsonUpdateDenied(): JsonResponse
    {
        return self::json(__('msg.denied'), false);
    }

    /**
     * JSON response with a single key/value pair.
     */
    public static function jsonWithSingleKey(string $msg, bool $ok = true): JsonResponse
    {
        return response()->json([$msg => $ok]);
    }

    // ------ Redirect helpers ------

    /**
     * Redirect to a route with a store success flash message.
     */
    public static function redirectStoreSuccess(string $routeName): RedirectResponse
    {
        return self::goWithSuccess($routeName, __('msg.store_ok'));
    }

    /**
     * Redirect to a route with a delete success flash message.
     */
    public static function redirectDeleteSuccess(string $routeName): RedirectResponse
    {
        return self::goWithSuccess($routeName, __('msg.del_ok'));
    }

    /**
     * Redirect to a route with an update success flash message.
     */
    public static function redirectUpdateSuccess(string $routeName): RedirectResponse
    {
        return self::goWithSuccess($routeName, __('msg.update_ok'));
    }

    /**
     * Redirect to a named route, optionally passing parameters.
     */
    public static function goToRoute(array|string $goto, int $status = 302, array $headers = [], ?bool $secure = null): RedirectResponse
    {
        $data = [];
        $to = (\is_array($goto) ? $goto[0] : $goto) ?: 'dashboard';
        if (\is_array($goto)) {
            array_shift($goto);
            $data = $goto;
        }

        return app('redirect')->to(route($to, $data), $status, $headers, $secure);
    }

    /**
     * Redirect to a route with a danger flash message (default: record not found).
     */
    public static function goWithDanger(string $to = 'dashboard', ?string $msg = null): RedirectResponse
    {
        $msg ??= __('msg.rnf');

        return self::goToRoute($to)->with('flash_danger', $msg);
    }

    /**
     * Redirect to a route with a success flash message.
     */
    public static function goWithSuccess(string $to, string $msg): RedirectResponse
    {
        return self::goToRoute($to)->with('flash_success', $msg);
    }

    // ------ Date and time helpers ------

    /**
     * Get the days of the week.
     */
    public static function getDaysOfTheWeek(): array
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }

    /**
     * Get currently valid exam announcements (not expired).
     */
    public static function getValidExamAnnounces(): \Illuminate\Database\Eloquent\Collection
    {
        return ExamAnnounce::where('duration', '>=', time())->get();
    }

    /**
     * Format a timestamp to a friendly date string.
     *
     * @param  mixed  $timestamp
     */
    public static function formatDate($timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }
        $datetime = new DateTime($timestamp);

        return $datetime->format('l, j F Y');
    }

    /**
     * Format a timestamp to a full date and time string.
     *
     * @param  mixed  $timestamp
     */
    public static function formatDateTime($timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }
        $datetime = new DateTime($timestamp);

        return $datetime->format('l, j F Y. h:i A');
    }

    // ------ Table/query helpers ------

    /**
     * Map of table identifiers to human‑readable names.
     */
    public static function getTableNames(): array
    {
        return [
            'books' => 'books',
            'my_classes' => 'classes',
            'class_types' => 'class types',
            'dorms' => 'dorms',
            'exams' => 'exams',
            'exam_categories' => 'exam categories',
            'grades' => 'grades',
            'payments' => 'payments',
            'subjects' => 'subjects',
            'student_records' => 'students',
            'staff_records' => 'staff',
            'users' => 'users',
            'exam_records' => 'exam records',
        ];
    }

    /**
     * Get the list of columns for a given table (used by query builder).
     */
    public static function getTableCols(string $table_name): ?array
    {
        return match ($table_name) {
            'users' => [
                'photo_path' => 'photo',
                'name' => 'full name',
                'religion' => 'religion',
                'primary_identification_number' => 'primary ID number',
                'secondary_identification_number' => 'secondary ID number',
                'occupation' => 'occupation',
                'address' => 'address',
                'email' => 'email',
                'gender' => 'gender',
                'date_of_birth' => 'date of birth',
                'primary_mobile' => 'primary mobile',
                'secondary_mobile' => 'secondary mobile',
            ],
            'subjects' => [
                'subjects.name as subject_name' => 'full name',
                'slug' => 'short name',
                'my_classes.name as class_name' => 'class name',
                'is_core' => 'is core',
            ],
            'student_records' => [
                'users.name as user' => 'full name',
                'users.religion as religion' => 'religion',
                'users.date_of_birth as date_of_birth' => 'date of birth',
                'users.gender as gender' => 'gender',
                'primary_school_name' => 'primary school',
                'secondary_school_name' => 'secondary school',
                'disability' => 'disability',
                'food_taboos' => 'food taboos',
                'admission_number' => 'admission number',
                'house_number' => 'house number',
                'my_classes.name as class' => 'class name',
                'sections.name as section' => 'section name',
                'dorms.name as dorm' => 'dormitory name',
                'dorm_room_no' => 'dormitory room number',
                'date_admitted' => 'date admitted',
                'users.address as address' => 'address',
                'parent_status' => 'parent status',
                'parents.name as parent' => 'parent name',
                'parents.primary_mobile' => 'parent primary mobile',
                'parents.secondary_mobile' => 'parent secondary mobile',
                'session' => 'session',
            ],
            'staff_records' => [
                'users.name as user' => 'full name',
                'users.username as username' => 'username',
                'users.email as email' => 'email',
                'users.date_of_birth as date_of_birth' => 'date of birth',
                'users.gender as gender' => 'gender',
                'users.religion as religion' => 'religion',
                'users.primary_identification_number' => 'primary ID number',
                'users.secondary_identification_number' => 'secondary ID number',
                'users.primary_mobile' => 'primary mobile',
                'users.secondary_mobile' => 'secondary mobile',
                'place_of_living' => 'place of living',
                'designation' => 'designation',
                'users.address as address' => 'address',
                'users.date_of_birth as birthday' => 'date of birth',
                'employment_date' => 'employment date',
                'employment_number' => 'employment number',
                'file_number' => 'file number',
                'tin_number' => 'tin number',
                'licence_number' => 'licence number',
                'bank_account_number' => 'bank account number',
                'bank_name' => 'bank name',
                'social_security_number' => 'Social Security number',
                'number_of_periods' => 'number of periods',
                'education_level' => 'education level',
                'college_attended' => 'college attended',
                'year_graduated' => 'year graduated',
                'subjects_studied' => 'subjects studied',
            ],
            'payments' => [
                'title' => 'title',
                'amount' => 'amount paid',
                'description' => 'description',
                'method' => 'payment method',
                'year' => 'year',
            ],
            'grades' => [
                'grades.name as grade_name' => 'name',
                'class_types.name as class_type_name' => 'class type',
                'mark_from' => 'mark from',
                'mark_to' => 'mark to',
                'point' => 'point',
                'remarks' => 'remarks',
            ],
            'exam_categories' => ['name' => 'name'],
            'exams' => ['name' => 'name', 'term' => 'term', 'year' => 'year'],
            'dorms' => ['name' => 'name', 'description' => 'description'],
            'class_types' => ['name' => 'name', 'code' => 'code', 'minimum_subjects_required' => 'subjects considered'],
            'my_classes' => ['name' => 'name'],
            'books' => [
                'name' => 'name',
                'author' => 'author',
                'description' => 'description',
                'book_type' => 'boot type',
                'url' => 'website address',
                'location' => 'location',
                'total_copies' => 'total copies',
                'issued_copies' => 'issued copies',
            ],
            'exam_records' => [
                'users.name as student' => 'student',
                'my_classes.name as class' => 'class',
                'sections.name as section' => 'section',
                'exams.name as exam' => 'exam',
                'total' => 'total',
                'average' => 'average',
                'grades.name as grade' => 'grade',
                'divisions.name as division' => 'division',
                'points' => 'points',
                'section_position' => 'section position',
                'class_position' => 'class position',
                'p_comment' => 'principal comment',
                't_comment' => 'teacher comment',
                'exam_records.year' => 'exam year',
            ],
            default => null,
        };
    }

    /**
     * Get a comma‑separated string of actual column names for a given table, filtered by requested columns.
     */
    public static function getSelectedTableColumns(string $name, array $columns = []): string
    {
        $cols = self::getTableCols($name);
        $flipped = array_flip($cols);

        if (\in_array('*', $columns)) {
            return implode(',', $flipped);
        }

        return implode(',', array_intersect($columns, $flipped));
    }

    /**
     * Get a presentable column mapping (original column name → human readable) filtered by requested columns.
     */
    public static function getPresentableTableColumns(string $name, array $columns = []): array
    {
        $data = self::getTableCols($name);
        $flipped = array_flip($data);

        if (\in_array('*', $columns)) {
            return $data;
        }
        $intersected = array_intersect($flipped, $columns);

        return array_flip($intersected);
    }

    /**
     * Generate a human‑readable "from … to … of total" string from a paginator instance.
     */
    public static function getPaginationFromToTotalAsString(LengthAwarePaginator $paginator): string
    {
        $total = $paginator->total();
        $perPage = $paginator->perPage();
        $currentPage = $paginator->currentPage();

        $from = ($currentPage == 1) ? 1 : $perPage * ($currentPage - 1) + 1;
        $to = $perPage * $currentPage;
        if ($currentPage == $paginator->lastPage()) {
            $to = $total;
        }

        return "$from to $to of $total";
    }

    /**
     * Check if the current academic session matches the current calendar year.
     */
    public static function currentSessionMatchesCurrentYear(): bool
    {
        $session = self::getCurrentSession();
        $year = explode('-', $session)[0];

        return $year === date('Y');
    }

    /**
     * Student statuses (active / graduated).
     */
    public static function getStudentStatuses(): array
    {
        return [1 => 'Active', 0 => 'Graduated'];
    }

    // ------ Image / file utilities ------

    /**
     * Convert an image file to a base64 data URI.
     */
    public static function imgToBase64(string $img_path): ?string
    {
        if (! file_exists($img_path)) {
            return null;
        }
        $type = pathinfo($img_path, PATHINFO_EXTENSION);
        $data = file_get_contents($img_path, false);

        return "data:image/$type;base64,".base64_encode($data);
    }

    /**
     * Return the supported paper sizes for Dompdf.
     */
    public static function getDompdfSupportedPaperSizes(): array
    {
        return CPDF::$PAPER_SIZES;
    }

    // ------ Google Analytics helpers ------

    /**
     * Check if Google Analytics is correctly set up and enabled.
     */
    public static function googleAnalyticsSetUpOkAndEnabled(): bool
    {
        $credentialFile = storage_path('/app/public/'.self::getSetting('google_analytic_service_account_credential_file'));

        return file_exists($credentialFile)
            && self::getSetting('google_analytic_property_id') !== null
            && self::getSetting('google_analytic_tag_id') !== null
            && (bool) self::getSetting('analytics_enabled') === true;
    }

    /**
     * Period options for Spatie Analytics package.
     */
    public static function getSpatieAnalyticsPackagePeriods(): array
    {
        return [
            'days' => 'Days',
            'months' => 'Months',
            'years' => 'Years',
        ];
    }

    /**
     * Return a query builder instance for a given table.
     */
    public static function getDbFacadesTable(string $table_name): Builder
    {
        return DB::table($table_name);
    }

    /**
     * Generate a text color (inverted) based on a background RGB color string.
     */
    public static function getContrastColor(string $rgb): ?string
    {
        $pattern = '/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/';
        $brightness = 1;
        $color = null;

        if (preg_match($pattern, $rgb, $matches)) {
            $red = (int) $matches[1];
            $green = (int) $matches[2];
            $blue = (int) $matches[3];

            $ired = floor((255 - $red) * $brightness);
            $igreen = floor((255 - $green) * $brightness);
            $iblue = floor((255 - $blue) * $brightness);

            $color = "rgb($ired,$igreen,$iblue)";
        }

        return $color;
    }

    /**
     * Get the application icon URL.
     */
    public static function getAppIcon(): string
    {
        return asset('images/icons/icon-512x512.png');
    }

    /**
     * Determine the path to the tenant‑specific ID card themes directory.
     */
    public static function getTenancyAwareIDCardsThemeDir(?int $tenant_id = null): string
    {
        $id = $tenant_id ?? tenant()?->getTenantKey();

        return resource_path().'/views/pages/students/id_cards/themes/'.config('tenancy.database.prefix').$id.'/';
    }

    /**
     * Determine the active notification channels based on notifiable preferences.
     *
     * @param  mixed  $notifiable
     */
    public static function getActiveNotificationChannels($notifiable, bool $include_database = false, bool $include_email = false, bool $include_push_notification = false, bool $include_sms = false): array
    {
        if (! $notifiable->notification_preferences || ! \is_array($notifiable->notification_preferences)) {
            return [];
        }

        $notification_preferences = $notifiable->notification_preferences;
        $channels = [];

        if ((bool) $notification_preferences['status'] === true) {
            if ($include_database) {
                $channels[] = 'database';
            }
            if (
                $notification_preferences['on_email_channel'] && $notifiable->hasVerifiedEmail()
                && self::getSetting('enable_email_notification') == 1 && $include_email
            ) {
                $channels[] = 'mail';
            }
            if ($notification_preferences['on_push_channel'] && self::getSetting('enable_push_notification') == 1 && $include_push_notification) {
                $channels[] = FcmChannel::class;
            }
            if ($notification_preferences['on_sms_channel'] && self::getSetting('enable_sms_notification') == 1 && $include_sms) {
                $channels[] = 'vonage';
            }
        }

        return $channels;
    }

    /**
     * Parse Markdown to HTML using Parsedown.
     */
    public static function parseMarkdown(string $input): string
    {
        $parsedown = new Parsedown;

        return $parsedown->text($input);
    }

    /**
     * List of model names that support soft‑deletion and can be emptied from the bin.
     */
    public static function getCanSoftDeleteModelNames(array $remove = [], $is_tenant_context = true): array
    {
        $names = $is_tenant_context ? ['users', 'payments', 'my_classes', 'exams', 'threads'] : ['users', 'tenants'];

        return $remove ? array_values(array_diff($names, $remove)) : $names;
    }

    public static function formatDomain(string $domain): string
    {
        if (str_contains($domain, 'localhost') || str_contains($domain, '127.0.0.1')) {
            $port = Str::afterLast(config('app.url'), ':');
            $port = $port === '' ? '8000' : $port;

            return "http://$domain:$port";
        }

        return "https://$domain";
    }
}
