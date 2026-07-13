<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Exports\StudentsBatchAddExport;
use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StudentRecordCreate;
use App\Http\Requests\Student\StudentRecordUpdate;
use App\Imports\StudentsBatchStoreImport;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Rules\HasOrderedClassDescriptionSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as HttpReq;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StudentRecordController extends Controller
{
    protected LocationRepo $loc;
    protected MyClassRepo $my_class;
    protected UserRepo $user;
    protected StudentRepo $student;
    protected string $students_batch_file_name_description;
    protected string $session;

    public function __construct(
        LocationRepo $loc,
        MyClassRepo $my_class,
        UserRepo $user,
        StudentRepo $student,
    ) {
        $this->permissionMap = config('permissions.names.tenant.student_records');
        parent::__construct();
        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->user = $user;
        $this->student = $student;
        $this->students_batch_file_name_description = 'students batch add template';
        $this->session = Usr::getCurrentSession();
    }

    /**
     * Reset a student's password to 'student'.
     */
    public function reset_password($st_id): RedirectResponse
    {
        $this->user->updateUsersByColumn($st_id, ['password' => Hash::make('student')]);

        return back()->with('flash_success', __('msg.p_reset'));
    }

    /**
     * Show form to create a new student record.
     */
    public function create(): View
    {
        $data['parents'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.parent')]);
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationalities();

        return view('pages.students.add', $data);
    }

    /**
     * Store a new student record.
     */
    public function store(StudentRecordCreate $req): JsonResponse
    {
        $user_data = $req->only(Usr::getUserRecordFields());
        $student_data = $req->only(Usr::getStudentDataFields());

        $class_type = $this->my_class->findClassTypeByClassId($req->my_class_id);
        $user_code = strtoupper(Str::random(10));
        $admission_number = $req->admission_number ?: mt_rand(1000, 99999);

        $role = config('roles.default_roles.tenant.student');

        $user_data['user_role'] = $role;
        $user_data['name'] = strtoupper($req->name);
        $user_data['code'] = $user_code;
        $user_data['password'] = Hash::make('student');
        $user_data['username'] = $this->generateStudentUsername($class_type->code, $student_data['date_admitted'], $admission_number);

        // Handle file uploads
        $this->handleFileUpload($req, 'photo', $user_data, $user_code, $role);
        $this->handleFileUpload($req, 'birth_certificate', $student_data, $user_code, $role);

        $user = $this->user->createUser($user_data);
        $user->assignRole($role);

        $student_data['user_id'] = $user->id;
        $student_data['admission_number'] = $user->username;
        $student_data['session'] = $this->session;
        $this->formatStudentDataFields($student_data);

        $this->student->createStudentRecord($student_data);

        return Usr::jsonStoreOk();
    }

    /**
     * Generate batch template Excel file.
     */
    public function batch_template(HttpReq $req): BinaryFileResponse
    {
        $user_headings = self::getUserHeadings(false);
        $student_headings = self::getStudentHeadings(false);

        $data['heading_names'] = [...$user_headings, ...$student_headings];
        $data['class'] = $class = $this->my_class->findClass($req->my_class_id);
        $data['my_class_id'] = $req->my_class_id;
        $data['nationality'] = $this->loc->getNationalityById($req->nationality_id);
        $data['nationality_id'] = $req->nationality_id;
        $data['state'] = $this->loc->getState($req->state_id);
        $data['state_id'] = $req->state_id;
        $data['lgas'] = $this->loc->getStateById($req->state_id);
        $data['sections'] = $this->my_class->getClassSections($req->my_class_id);
        $data['dormitories'] = $this->student->getAllDorms();
        $data['genders'] = Usr::getGenders();
        $data['disabilities'] = Usr::getDisabilities();
        $data['religions'] = Usr::getReligions();
        $data['blood_groups'] = Usr::getBloodGroups();
        $data['year'] = $year = Usr::getCurrentSession();

        $delimiter = Usr::getDelimiter();
        $time = time();
        $filename = ucfirst(strtolower(str_replace(' ', '_', "{$class->name}{$delimiter}{$this->students_batch_file_name_description}{$delimiter}{$year}{$delimiter}{$time}.xlsx")));

        return Excel::download(
            new StudentsBatchAddExport($data),
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
     * Get user record headings.
     */
    public static function getUserHeadings(bool $values_only = true): array
    {
        return Usr::getUserRecordFields([], $values_only);
    }

    /**
     * Get student record headings.
     */
    public static function getStudentHeadings(bool $values_only = true): array
    {
        return Usr::getStudentDataFields(['my_parent_id', 'age', 'birth_certificate'], $values_only);
    }

    /**
     * Import batch student data from Excel.
     */
    public function batch_store(HttpReq $req): JsonResponse
    {
        $template = $req->file('template');
        $delimiter = Usr::getDelimiter();
        // Remove file extension and unix timestamp
        $name = Str::beforeLast(pathinfo($template->getClientOriginalPath(), PATHINFO_FILENAME), $delimiter);
        $req['template_file_name'] = $name;

        Validator::make($req->toArray(), [
            'template' => 'required|file|mimes:xlsx,xlx|max:4096',
            'template_file_name' => new HasOrderedClassDescriptionSession($this->students_batch_file_name_description),
        ], [], ['template' => 'students batch add template'])->validate();

        try {
            Excel::import(new StudentsBatchStoreImport(), $template);
            return Usr::jsonStoreOk();
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $errors = [];

            foreach ($e->failures() as $failure) {
                $actual_row = $failure->row() + 1; // 1 = The very first data row (visible heading row, row 5, skipped)
                $errors[$failure->attribute()] = "There was an error on row $actual_row. " . implode(', ', $failure->errors());
            }

            return Usr::json(arr: ['errors' => $errors, 'hasAjaxErrors' => true]);
        }
    }

    /**
     * List students by class ID.
     */
    public function list_by_class($class_id): View|RedirectResponse
    {
        $validator = Validator::make(['class_id' => $class_id], [
            'class_id' => 'required|exists:my_classes,id',
        ]);

        if ($validator->fails()) {
            return Usr::goWithDanger();
        }

        $data['class_id'] = $class_id;
        $data['students'] = $this->student->getActiveStudentsByClass($class_id, ['section', 'user', 'my_parent']);
        $data['sections'] = $this->my_class->getClassSections($class_id);

        return view('pages.students.list', $data);
    }

    /**
     * Display graduated students.
     */
    public function graduated(): View
    {
        $data['grad_students'] = $this->student->getAllGraduatedPromotionsWithDetails()->whereNotNull('user');

        return view('pages.students.graduated', $data);
    }

    /**
     * Mark a student as not graduated and remove promotion record.
     */
    public function mark_not_graduated($sr_id, $prom_id)
    {
        $d['is_graduated'] = false;
        $d['graduated_session'] = NULL;
        $d['session'] = $this->session;

        $this->student->updateStudentRecords($sr_id, $d);
        $this->student->deletePromotion($prom_id);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Show own profile.
     */
    #[SkipPermission]
    public function show_profile($student)
    {
        return $this->show($student);
    }

    /**
     * Show student record details.
     */
    public function show($student): View|RedirectResponse
    {
        if (!$student) {
            return Usr::goWithDanger();
        }

        if ($this->isRestrictedStudentView($student)) {
            return redirect()->route('dashboard')->with('pop_error', __('msg.denied'));
        }

        $data['sr'] = $this->findStudentRecord($student);

        return view('pages.students.show', $data);
    }

    /**
     * Show form to edit a student record.
     */
    public function edit($student): View|RedirectResponse
    {
        if (!$student) {
            return Usr::goWithDanger();
        }

        $data['sr'] = $sr = $this->findStudentRecord($student);
        $data['parents'] = $this->user->getUsersByRoleNames([config('roles.default_roles.tenant.parent')]);
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationalities();
        $data['class_sections'] = $this->my_class->getClassSections($sr->my_class_id);

        return view('pages.students.edit', $data);
    }

    /**
     * Update a student record.
     */
    public function update(StudentRecordUpdate $req, $student): JsonResponse
    {
        if (!$student) {
            return Usr::json(__('msg.srnf'), false);
        }

        $sr = $this->findStudentRecord($student);
        $user_code = $sr->user->code;

        $user_data = $req->only(Usr::getUserRecordFields());
        $user_data['name'] = strtoupper($req->name);
        $user_data['username'] = $req->admission_number;

        $this->handleFileUpload($req, 'photo', $user_data, $user_code, 'student');
        $this->user->updateUsersByColumn($sr->user->id, $user_data);

        $student_data = $req->only(Usr::getStudentDataFields());
        $this->handleFileUpload($req, 'birth_certificate', $student_data, $user_code, 'student');

        $this->formatStudentDataFields($student_data);
        $student_data['admission_number'] = $req->admission_number;

        $this->student->updateStudentRecords($student, $student_data);

        // Optional: Delete old marks if class/section changed
        // Mk::deleteOldClassRecords($sr->user->id, $student_data['my_class_id']);

        return Usr::jsonUpdateOk();
    }

    /**
     * Block all students in a class.
     */
    public function block_all_class(HttpReq $req): JsonResponse
    {
        $student_ids = $this->getUserIdsByClass($req->class_id);
        $this->user->updateByIds($student_ids, ['is_blocked' => true]);

        return Usr::json('Selected class students have been blocked successfully.');
    }

    /**
     * Unblock all students in a class.
     */
    public function unblock_all_class(HttpReq $req): JsonResponse
    {
        $student_ids = $this->getUserIdsByClass($req->class_id);
        $this->user->updateByIds($student_ids, ['is_blocked' => false]);

        return Usr::json('Selected class students have been unblocked successfully.');
    }

    /**
     * Block all graduated students.
     */
    public function block_all_graduated(): JsonResponse
    {
        $student_ids = $this->student->getAllGraduatedPromotions()->pluck('user_id');
        $this->user->updateByIds($student_ids, ['is_blocked' => true]);

        return Usr::json('Graduated students have been blocked successfully.');
    }

    /**
     * Unblock all graduated students.
     */
    public function unblock_all_graduated(): JsonResponse
    {
        $student_ids = $this->student->getAllGraduatedPromotions()->pluck('user_id');
        $this->user->updateByIds($student_ids, ['is_blocked' => false]);

        return Usr::json('Graduated students have been unblocked successfully.');
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Generate student username based on app code, class code, admission year, and admission number.
     */
    protected function generateStudentUsername(string $class_code, string $date_admitted, $admission_number): string
    {
        $year = date('Y', strtotime($date_admitted));
        return strtoupper(Usr::getSystemShortCode() . '/' . $class_code . '/' . $year . '/' . $admission_number);
    }

    /**
     * Handle file upload for user or student data.
     */
    protected function handleFileUpload(HttpReq $req, string $field, array &$data, string $user_code, string $role): void
    {
        if (!$req->hasFile($field)) {
            return;
        }

        $file = $req->file($field);
        $file_meta = Usr::getFileMetaData($file);
        $file_meta['name'] = "$field." . $file_meta['ext'];
        $file_meta['path'] = Usr::getUploadPath($role) . $user_code . '/' . $file_meta['name'];

        $file->storeAs($file_meta['path']);
        $data['photo_path'] = $file_meta['path'];
    }

    /**
     * Format student data fields according to business rules.
     */
    protected function formatStudentDataFields(array &$student_data): void
    {
        $student_data['house_number'] = strtoupper($student_data['house_number'] ?? '');
        $student_data['primary_school_name'] = ucfirst($student_data['primary_school_name'] ?? '');
        $student_data['secondary_school_name'] = ucfirst($student_data['secondary_school_name'] ?? '');
        $student_data['parent_status'] = ucfirst($student_data['parent_status'] ?? '');
    }

    /**
     * Find a student record by ID (active or graduated).
     */
    protected function findStudentRecord($id)
    {
        return $this->student->getActiveStudentsQueryWithRelations(['id' => $id])->first()
            ?? $this->student->getGraduatedStudentsQuery(['id' => $id])->first();
    }

    /**
     * Check if current user is restricted from viewing a student profile.
     */
    protected function isRestrictedStudentView($student): bool
    {
        $user = auth()->user();

        return $student != $user->id && !$user->hasAnyRole(Usr::getTenancyAwareStaffRoles());
    }

    /**
     * Get student user IDs for a given class.
     */
    protected function getUserIdsByClass(int $class_id): array
    {
        return $this->student->getActiveStudentsQueryWithRelations(['my_class_id' => $class_id])
            ->get()
            ->pluck('user_id')
            ->toArray();
    }
}
