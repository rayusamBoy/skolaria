<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Rules\StartsWithProperPhoneCode;
use App\Rules\Uppercase;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StudentIDCardController extends Controller
{
    protected $my_class, $student;

    public function __construct(MyClassRepo $my_class, StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.student_id_cards');
        parent::__construct();
        $this->my_class = $my_class;
        $this->student = $student;
    }

    public function index()
    {
        $d['theme_names'] = $this->getThemeFilesNames();
        $d['selected'] = false;

        return view('pages.students.id_cards.index', $d);
    }

    public function manage(Request $req)
    {
        Validator::make($req->toArray(), [
            'my_class_id' => 'required|exists:my_classes,id',
            'expire_date' => 'required|string',
            'phone' => ['required', 'string', 'min:10', 'max:16', new StartsWithProperPhoneCode],
            'class_from' => 'sometimes|nullable|exists:my_classes,name',
            'class_to' => 'sometimes|nullable|exists:my_classes,name',
            'motto' => ['required', 'string', new Uppercase],
            'brightness' => 'sometimes|nullable|decimal:0,2|min:1|max:100',
        ], [], ['my_class_id' => 'Class', 'phone' => 'phone number'])->validate();

        $d = $req->except('_token');

        if ($req->filled(['my_class_id', 'issued_date', 'expire_date', 'id_theme'])) {
            // Exclude students with soft deleted user record
            $d['students'] = $st = $this->student->whereRecord(['my_class_id' => $req->my_class_id])->get()->whereNotNull('user')->sortBy('user.name');
            if ($st->count() < 1)
                return Qs::goWithDanger('students.id_cards');

            $d['theme_names'] = $this->getThemeFilesNames();
            $d['selected'] = true;
        }

        return view('pages.students.id_cards.index', $d);
    }

    public function print_class($class_id, $issued, $expire, $theme, $phone, $class_from = NULL, $class_to = NULL, $motto, $brightness = 1, $website_link = NULL)
    {
        $d['students'] = $this->student->whereRecord(['my_class_id' => $class_id])->with('my_class')->get()->whereNotNull('user')->sortBy('user.name');
        $d['issued'] = $issued;
        $d['expire'] = $expire;
        $d["year_from"] = explode(",", $issued)[1];
        $d["year_to"] = explode(",", $expire)[1];
        $d['phone'] = $phone;
        $d['class_from'] = $class_from;
        $d['class_to'] = $class_to;
        $d['motto'] = $motto;
        $d['brightness'] = $brightness;
        $d['web_link'] = $website_link;
        $d['settings'] = Qs::getSettings();

        // Theme name should be a four digit code name like '0001' etc.
        return view('pages.students.id_cards.themes.' . config('tenancy.database.prefix') . tenant()?->getTenantKey() . '/' . $theme, $d);
    }

    public function print_selected(Request $req)
    {
        // If no student(s) selected at all
        if ($req->students_ids == null)
            return Qs::goWithDanger('students.id_cards', 'You must select at least a student to print ID card');

        $d = $req->except('_token');
        $d['students'] = $this->student->getRecordByUserIDs($req->students_ids)->with('my_class')->get()->sortBy('user.name');
        $d["year_from"] = explode(",", $req->issued)[1];
        $d["year_to"] = explode(",", $req->expire)[1];
        $d['phone'] = $req->phone;
        $d['settings'] = Qs::getSettings();

        return view('pages.students.id_cards.themes.' . config('tenancy.database.prefix') . tenant()?->getTenantKey() . '/' . $d['theme'], $d);
    }

    private function getThemeFilesNames()
    {
        $id_card_theme_dir = Qs::getTenancyAwareIDCardsThemeDir();
        $tenant_storage_dir = tenancy()->run(tenant(), fn() => storage_path("app/public/"));

        $file_names_to_copy = ['0001.blade.php'];
        $dir_names_to_copy = ['backgrounds', 'others'];
        $default_dir = resource_path() . '/views/pages/students/id_cards/themes/samples/';

        if (!File::isDirectory($id_card_theme_dir)) {
            File::makeDirectory($id_card_theme_dir, 0755, true); // Make it a directory if not yet there

            foreach ($file_names_to_copy as $fname) {
                File::copy("$default_dir$fname", "$id_card_theme_dir$fname");
            }
        }

        foreach ($dir_names_to_copy as $dname) {
            if (!File::isDirectory("$tenant_storage_dir$dname")) {
                File::copyDirectory("$default_dir$dname", "$tenant_storage_dir$dname");
            }
        }

        $exclude = ['.', '..', ...$dir_names_to_copy];
        $files = array_diff(scandir($id_card_theme_dir), $exclude);
        array_walk_recursive($files, function (&$arr) {
            $arr = explode(".", $arr)[0]; // Leave out '.blade.php' part
        });

        return $files;
    }
}
