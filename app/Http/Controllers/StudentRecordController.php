<?php

namespace App\Http\Controllers;

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
        $this->user->update($st_id, ['password' => Hash::make('student')]);

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
        $data['nationals'] = $this->loc->getAllNationals();

        return view('pages.students.add', $data);
    }

    /**
     * Store a new student record.
     */
    public function store(StudentRecordCreate $req): JsonResponse
    {
        $user_data = $req->only(Usr::getUserRecord());
        $student_data = $req->only(Usr::getStudentData());

        $class_type = $this->my_class->findTypeByClass($req->my_class_id);
        $user_code = strtoupper(Str::random(10));
        $adm_no = $req->adm_no ?: mt_rand(1000, 99999);

        $role = config('roles.default_roles.tenant.student');

        $user_data['user_role'] = $role;
        $user_data['name'] = strtoupper($req->name);
        $user_data['code'] = $user_code;
        $user_data['password'] = Hash::make('student');
        $user_data['username'] = $this->generateStudentUsername($class_type->code, $student_data['date_admitted'], $adm_no);

        // Handle file uploads
        $this->handleFileUpload($req, 'photo', $user_data, $user_code, $role);
        $this->handleFileUpload($req, 'birth_certificate', $student_data, $user_code, $role);

        $user = $this->user->create($user_data);
        $user->assignRole($role);

        $student_data['user_id'] = $user->id;
        $student_data['adm_no'] = $user->username;
        $student_data['session'] = $this->session;
        $this->formatStudentDataFields($student_data);

        $this->student->createRecord($student_data);

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
        $data['class'] = $class = $this->my_class->find($req->my_class_id);
        $data['my_class_id'] = $req->my_class_id;
        $data['nationality'] = $this->loc->getNationality($req->nal_id);
        $data['nal_id'] = $req->nal_id;
        $data['state'] = $this->loc->getState($req->state_id);
        $data['state_id'] = $req->state_id;
        $data['lgas'] = $this->loc->getLGAs($req->state_id);
        $data['sections'] = $this->my_class->getClassSections($req->my_class_id);
        $data['dormitories'] = $this->student->getAllDorms();
        $data['genders'] = Usr::getGenders();
        $data['disabilities'] = Usr::getDisabilities();
        $data['religions'] = Usr::getReligions();
        $data['blood_groups'] = Usr::getBloodGroups();
        $data['year'] = $year = Usr::getCurrentSession();

        $delimiter = Usr::getDelimiter();
        $filename = ucfirst(strtolower(str_replace(' ', '_', "{$class->name}{$delimiter}{$this->students_batch_file_name_description}{$delimiter}{$year}.xlsx")));

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
        return Usr::getUserRecord([], $values_only);
    }

    /**
     * Get student record headings.
     */
    public static function getStudentHeadings(bool $values_only = true): array
    {
        return Usr::getStudentData(['my_parent_id', 'age', 'birth_certificate'], $values_only);
    }

    /**
     * Import batch student data from Excel.
     */
    public function batch_store(HttpReq $req): JsonResponse
    {
        $template = $req->file('template');
        $file_meta = Usr::getFileMetaData($template);
        $name = str_replace('.' . $file_meta['ext'], '', $file_meta['name']);
        $req['template_name'] = $name;

        Validator::make($req->toArray(), [
            'template' => 'required|file|mimes:xlsx,xlx|max:4096',
            'template_name' => new HasOrderedClassDescriptionSession($this->students_batch_file_name_description),
        ], [], ['template' => 'students batch add template'])->validate();

        Excel::import(new StudentsBatchStoreImport(), $template);

        return Usr::jsonStoreOk();
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
        $data['students'] = $this->student->findStudentsByClass($class_id, ['section', 'user', 'my_parent']);
        $data['sections'] = $this->my_class->getClassSections($class_id);

        return view('pages.students.list', $data);
    }

    /**
     * Display graduated students.
     */
    public function graduated(): View
    {
        $data['grad_students'] = $this->student->allGradStudents()->whereNotNull('user');

        return view('pages.students.graduated', $data);
    }

    /**
     * Mark a student as not graduated and remove promotion record.
     */
    public function mark_not_graduated($sr_id, $prom_id)
    {
        $d['grad'] = 0;
        $d['grad_date'] = NULL;
        $d['session'] = $this->session;

        $this->student->updateRecord($sr_id, $d);
        $this->student->deletePromotion($prom_id);

        return back()->with('flash_success', __('msg.update_ok'));
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
        $data['nationals'] = $this->loc->getAllNationals();
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

        $user_data = $req->only(Usr::getUserRecord());
        $user_data['name'] = strtoupper($req->name);
        $user_data['username'] = $req->adm_no;

        $this->handleFileUpload($req, 'photo', $user_data, $user_code, 'student');
        $this->user->update($sr->user->id, $user_data);

        $student_data = $req->only(Usr::getStudentData());
        $this->handleFileUpload($req, 'birth_certificate', $student_data, $user_code, 'student');

        $this->formatStudentDataFields($student_data);
        $student_data['adm_no'] = $req->adm_no;

        $this->student->updateRecord($student, $student_data);

        // Optional: Delete old marks if class/section changed
        // Mk::deleteOldRecord($sr->user->id, $student_data['my_class_id']);

        return Usr::jsonUpdateOk();
    }

    /**
     * Block all students in a class.
     */
    public function block_all_class(HttpReq $req): JsonResponse
    {
        $student_ids = $this->getUserIdsByClass($req->class_id);
        $this->user->updateByIds($student_ids, ['blocked' => 1]);

        return Usr::json('Selected class students have been blocked successfully.');
    }

    /**
     * Unblock all students in a class.
     */
    public function unblock_all_class(HttpReq $req): JsonResponse
    {
        $student_ids = $this->getUserIdsByClass($req->class_id);
        $this->user->updateByIds($student_ids, ['blocked' => 0]);

        return Usr::json('Selected class students have been unblocked successfully.');
    }

    /**
     * Block all graduated students.
     */
    public function block_all_graduated(): JsonResponse
    {
        $student_ids = $this->student->allGradOnlyStudents()->pluck('user_id');
        $this->user->updateByIds($student_ids, ['blocked' => 1]);

        return Usr::json('Graduated students have been blocked successfully.');
    }

    /**
     * Unblock all graduated students.
     */
    public function unblock_all_graduated(): JsonResponse
    {
        $student_ids = $this->student->allGradOnlyStudents()->pluck('user_id');
        $this->user->updateByIds($student_ids, ['blocked' => 0]);

        return Usr::json('Graduated students have been unblocked successfully.');
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Generate student username based on app code, class code, admission year, and admission number.
     */
    protected function generateStudentUsername(string $class_code, string $date_admitted, $adm_no): string
    {
        $year = date('Y', strtotime($date_admitted));
        return strtoupper(Usr::getAppCode() . '/' . $class_code . '/' . $year . '/' . $adm_no);
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
        $data[$field] = $file_meta['path'];
    }

    /**
     * Format student data fields according to business rules.
     */
    protected function formatStudentDataFields(array &$student_data): void
    {
        $student_data['house_no'] = strtoupper($student_data['house_no'] ?? '');
        $student_data['ps_name'] = ucfirst($student_data['ps_name'] ?? '');
        $student_data['ss_name'] = ucfirst($student_data['ss_name'] ?? '');
        $student_data['p_status'] = ucfirst($student_data['p_status'] ?? '');
    }

    /**
     * Find a student record by ID (active or graduated).
     */
    protected function findStudentRecord($id)
    {
        return $this->student->whereRecord(['id' => $id])->first()
            ?? $this->student->whereGraduatesRecord(['id' => $id])->first();
    }

    /**
     * Check if current user is restricted from viewing a student profile.
     */
    protected function isRestrictedStudentView($student): bool
    {
        $user = auth()->user();

        if ($student != $user->id || $user->hasAnyRole(Usr::getTenancyAwareStaffRoles())) {
            return false;
        }

        return true;
    }

    /**
     * Get student user IDs for a given class.
     */
    protected function getUserIdsByClass(int $class_id): array
    {
        return $this->student->whereRecord(['my_class_id' => $class_id])
            ->get()
            ->pluck('user_id')
            ->toArray();
    }
}
