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
use Illuminate\Http\Request as HttpReq;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StudentRecordController extends Controller
{
    protected $loc, $my_class, $user, $student, $students_batch_file_name_description;

    public function __construct(LocationRepo $loc, MyClassRepo $my_class, UserRepo $user, StudentRepo $student)
    {
        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->user = $user;
        $this->student = $student;
        $this->students_batch_file_name_description = 'students batch add template';
    }

    public function reset_pass($st_id)
    {
        $st_id = Usr::decodeHash($st_id);
        $data['password'] = Hash::make('student');
        $this->user->update($st_id, $data);

        return back()->with('flash_success', __('msg.p_reset'));
    }

    public function create()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUsersByRoleNames(['Parent']);
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();

        return view('pages.students.add', $data);
    }

    public function store(StudentRecordCreate $req)
    {
        $data = $req->only(Usr::getUserRecord());
        $sr = $req->only(Usr::getStudentData());

        $ct = $this->my_class->findTypeByClass($req->my_class_id)->code;

        $data['user_role'] = $user_role = 'student';
        $data['name'] = $name = strtoupper($req->name);
        $data['code'] = $code = strtoupper(Str::random(10));
        $data['password'] = Hash::make('student');
        $data['photo'] = Usr::createAvatar($name, $code, $user_role);
        $adm_no = $req->adm_no;
        $data['username'] = strtoupper(Usr::getAppCode() . '/' . $ct . '/' . date('Y', strtotime($sr['date_admitted'])) . '/' . ($adm_no ?: mt_rand(1000, 99999)));

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $data['photo'] = Usr::getUploadPath('student') . $data['code'] . '/' . $f['name'];
            $photo->storeAs($f['path']);
        }

        if ($req->hasFile('birth_certificate')) {
            $birth_certificate = $req->file('birth_certificate');
            $f = Usr::getFileMetaData($birth_certificate);
            $f['name'] = 'birth_certificate.' . $f['ext'];
            $f['path'] = $sr['birth_certificate'] = Usr::getUploadPath('student') . $data['code'] . '/' . $f['name'];
            $birth_certificate->storeAs($f['path']);
        }

        $user = $this->user->create($data);

        $sr['user_id'] = $user->id;
        $sr['adm_no'] = $user->username;
        $sr['session'] = Usr::getSetting('current_session');
        // Format string according to the enforced rule
        $sr['house_no'] = strtoupper($sr['house_no']);
        $sr['ps_name'] = ucfirst($sr['ps_name']);
        $sr['ss_name'] = ucfirst($sr['ss_name']);
        $sr['p_status'] = ucfirst($sr['p_status']);

        $this->student->createRecord($sr);

        return Usr::jsonStoreOk();
    }

    public function batch_template(HttpReq $req)
    {
        $ur = self::getUserHeadings(false);
        $sr = self::getStudentHeadings(false);

        $data['heading_names'] = [...$ur, ...$sr];
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

        return Excel::download(new StudentsBatchAddExport($data), ucfirst(strtolower(str_replace(' ', '_', "{$class->name}{$delimiter}{$this->students_batch_file_name_description}{$delimiter}{$year}.xlsx"))));
    }

    public static function getUserHeadings($values_only = true)
    {
        $ur = Usr::getUserRecord([], $values_only);
        return $ur;
    }

    public static function getStudentHeadings($values_only = true)
    {
        $sr = Usr::getStudentData(['my_parent_id', 'age', 'birth_certificate'], $values_only);
        return $sr;
    }

    public function batch_store(HttpReq $req)
    {
        $template = $req->file('template');
        $f = Usr::getFileMetaData($template);
        // Remove '.' and extension by replacing it with empty string
        $name = str_replace('.' . $f['ext'], '', $f['name']);
        $req['template_name'] = $name;

        Validator::make($req->toArray(), [
            'template' => 'required|file|mimes:xlsx,xlx|max:4096',
            'template_name' => new HasOrderedClassDescriptionSession($this->students_batch_file_name_description),
        ], [], ['template' => 'students batch add template'])->validate();

        if ($req->hasFile('template')) {
            Excel::import(new StudentsBatchStoreImport(), request()->file('template'));
            return Usr::jsonStoreOk();
        }
    }

    public function list_by_class($class_id)
    {
        $data['my_class'] = $mc = $this->my_class->getMC(['id' => $class_id])->first();
        $data['students'] = $this->student->findStudentsByClass($class_id);
        $data['sections'] = $this->my_class->getClassSections($class_id);

        return $mc === null ? Usr::goWithDanger() : view('pages.students.list', $data);
    }

    public function graduated()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['grad_students'] = $this->student->allGradStudents()->whereNotNull('user');

        return view('pages.students.graduated', $data);
    }

    public function not_graduated($sr_id, $prom_id)
    {
        $d['grad'] = 0;
        $d['grad_date'] = NULL;
        $d['session'] = Usr::getSetting('current_session');
        $sr_id = Usr::decodeHash($sr_id);

        $this->student->updateRecord($sr_id, $d);
        $this->student->deletePromotion($prom_id);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function show($sr_id)
    {
        $sr_id = Usr::decodeHash($sr_id);
        if (!$sr_id)
            return Usr::goWithDanger();

        // Try getRecord for the active students, or getRecord2 for the graduated students.
        $data['sr'] = $sr = $this->student->getRecord(['id' => $sr_id])->first() ?? $this->student->getRecord2(['id' => $sr_id])->first();

        /* Prevent Other Students/Parents from viewing Profile of others */
        //  if (auth()->id() != $sr->user_id && !Usr::userIsTeamSATCL() && !Usr::userIsMyChild($sr->user_id, auth()->id()))
        if ($sr->user->hasRole(config('roles.default_roles.tenant.student')) && !(auth()->user()->hasRole(config('roles.default_roles.tenant.librarian')) or auth()->user()->hasRole(config('roles.default_roles.tenant.accountant'))))
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));

        return view('pages.students.show', $data);
    }

    public function edit($sr_id)
    {
        $sr_id = Usr::decodeHash($sr_id);
        if (!$sr_id)
            return Usr::goWithDanger();

        $data['sr'] = $sr = $this->student->getRecord(['id' => $sr_id])->first() ?? $this->student->getRecord2(['id' => $sr_id])->first();
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUsersByRoleNames(['Parent']);
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        $data['class_sections'] = $this->my_class->getClassSections($sr->my_class_id);

        return view('pages.students.edit', $data);
    }

    public function update(StudentRecordUpdate $req, $sr_id)
    {
        $sr_id = Usr::decodeHash($sr_id);
        if (!$sr_id)
            return Usr::goWithDanger();

        $sr = $this->student->getRecord(['id' => $sr_id])->first() ?? $this->student->getRecord2(['id' => $sr_id])->first();
        $d = $req->only(Usr::getUserRecord());
        $d['name'] = strtoupper($req->name);
        $d['username'] = $req->adm_no;

        if ($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Usr::getFileMetaData($photo);
            $f['name'] = 'photo.' . $f['ext'];
            $f['path'] = $d['photo'] = Usr::getUploadPath('student') . $sr->user->code . '/' . $f['name'];
            $photo->storeAs($f['path']);
        }

        $this->user->update($sr->user->id, $d); // Update User Details

        $srec = $req->only(Usr::getStudentData());

        if ($req->hasFile('birth_certificate')) {
            $birth_certificate = $req->file('birth_certificate');
            $f = Usr::getFileMetaData($birth_certificate);
            $f['name'] = 'birth_certificate.' . $f['ext'];
            $f['path'] = $srec['birth_certificate'] = Usr::getUploadPath('student') . $sr->user->code . '/' . $f['name'];
            $birth_certificate->storeAs($f['path']);
        }

        // Format string according to the enforced rules
        $srec['house_no'] = strtoupper($srec['house_no']);
        $srec['ps_name'] = ucfirst($srec['ps_name']);
        $srec['ss_name'] = ucfirst($srec['ss_name']);
        $srec['p_status'] = ucfirst($srec['p_status']);

        $srec['adm_no'] = $req->adm_no;
        $this->student->updateRecord($sr_id, $srec); // Update Student Record

        /*** If Class/Section is Changed in Same Year, Delete Marks/ExamRecord of Previous Class/Section ****/
        // Mk::deleteOldRecord($sr->user->id, $srec['my_class_id']);

        return Usr::jsonUpdateOk();
    }

    public function destroy($st_id)
    {
        $st_id = Usr::decodeHash($st_id);
        if (!$st_id)
            return Usr::goWithDanger();

        $sr = $this->student->getRecord(['user_id' => $st_id])->first();
        $this->user->delete($sr->user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    public function block_all_class(HttpReq $req)
    {
        $students_ids = $this->student->getRecord(['my_class_id' => $req->class_id])->get()->pluck('user_id');
        $this->user->updateByIds($students_ids, ['blocked' => 1]);

        return Usr::json("Selected class sutdents have been Blocked successfully");
    }

    public function unblock_all_class(HttpReq $req)
    {
        $students_ids = $this->student->getRecord(['my_class_id' => $req->class_id])->get()->pluck('user_id');
        $this->user->updateByIds($students_ids, ['blocked' => 0]);

        return Usr::json("Selected class sutdents have been Unblocked successfully");
    }

    public function block_all_graduated()
    {
        $students_ids = $this->student->allGradOnlyStudents()->pluck('user_id');
        $this->user->updateByIds($students_ids, ['blocked' => 1]);

        return Usr::json("Graduated sutdents have been Blocked successfully");
    }

    public function unblock_all_graduated()
    {
        $students_ids = $this->student->allGradOnlyStudents()->pluck('user_id');
        $this->user->updateByIds($students_ids, ['blocked' => 0]);

        return Usr::json("Graduated have been Unblocked successfully");
    }
}
