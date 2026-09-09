<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Rules\StartsWithProperPhoneCode;
use App\Rules\Uppercase;
use File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class StudentIDCardController extends Controller
{
    protected MyClassRepo $my_class;

    protected StudentRepo $student;

    public function __construct(MyClassRepo $my_class, StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.student_id_cards');
        parent::__construct();
        $this->my_class = $my_class;
        $this->student = $student;
    }

    /**
     * Show the ID card designer page.
     */
    public function index(): View
    {
        $data['theme_names'] = $this->getThemeFilesNames();
        $data['selected'] = false;

        return view('pages.students.id_cards.index', $data);
    }

    /**
     * Handle the ID card settings form and load the student list.
     */
    public function manage(Request $req): View|RedirectResponse
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

        $data = $req->except('_token');

        if ($req->filled(['my_class_id', 'issued_date', 'expire_date', 'id_theme'])) {
            $students = $this->student->getActiveStudentsQueryWithRelations([
                'my_class_id' => $req->my_class_id,
            ])->get()->whereNotNull('user')->sortBy('user.name');

            if ($students->count() < 1) {
                return Qs::goWithDanger('students.id_cards');
            }

            $data['students'] = $students;
            $data['theme_names'] = $this->getThemeFilesNames();
            $data['selected'] = true;
        }

        return view('pages.students.id_cards.index', $data);
    }

    /**
     * Print ID cards for an entire class.
     */
    public function print_class(
        $class_id,
        $issued,
        $expire,
        $theme,
        $phone,
        $class_from,
        $class_to,
        $motto,
        $brightness = 1,
        $website_link = null
    ): View {
        $data['students'] = $this->student->getActiveStudentsQueryWithRelations([
            'my_class_id' => $class_id,
        ])->with('my_class')->get()->whereNotNull('user')->sortBy('user.name');

        $data['issued'] = $issued;
        $data['expire'] = $expire;
        $data['year_from'] = explode(',', $issued)[1] ?? '';
        $data['year_to'] = explode(',', $expire)[1] ?? '';
        $data['phone'] = $phone;
        $data['class_from'] = $class_from;
        $data['class_to'] = $class_to;
        $data['motto'] = $motto;
        $data['brightness'] = $brightness;
        $data['web_link'] = $website_link;
        $data['settings'] = Qs::getSettings();

        return view(
            'pages.students.id_cards.themes.'.config('tenancy.database.prefix').tenant()?->getTenantKey().'/'.$theme,
            $data
        );
    }

    /**
     * Print ID cards for selected students.
     */
    public function print_selected(Request $req): View|RedirectResponse
    {
        if ($req->students_ids == null) {
            return Qs::goWithDanger('students.id_cards', 'You must select at least a student to print ID card');
        }

        $data = $req->except('_token');
        $data['students'] = $this->student->getActiveStudentsByUserIdsQuery($req->students_ids)
            ->with('my_class')
            ->get()
            ->sortBy('user.name');
        $data['year_from'] = explode(',', $req->issued)[1] ?? '';
        $data['year_to'] = explode(',', $req->expire)[1] ?? '';
        $data['phone'] = $req->phone;
        $data['settings'] = Qs::getSettings();

        return view(
            'pages.students.id_cards.themes.'.config('tenancy.database.prefix').tenant()?->getTenantKey().'/'.$data['theme'],
            $data
        );
    }

    /**
     * Get the list of available ID card theme file names.
     * Copies sample files to the tenant directory if missing.
     */
    private function getThemeFilesNames(): array
    {
        $id_card_theme_dir = Qs::getTenancyAwareIDCardsThemeDir();
        $tenant_storage_dir = tenancy()->run(tenant(), fn () => storage_path('app/public/'));

        $file_names_to_copy = ['0001.blade.php'];
        $dir_names_to_copy = ['backgrounds', 'others'];
        $default_dir = resource_path('views/pages/students/id_cards/themes/samples/');

        if (! File::isDirectory($id_card_theme_dir)) {
            File::makeDirectory($id_card_theme_dir, 0755, true);
            foreach ($file_names_to_copy as $fname) {
                File::copy("$default_dir$fname", "$id_card_theme_dir$fname");
            }
        }

        foreach ($dir_names_to_copy as $dname) {
            $dest = "$tenant_storage_dir$dname";
            if (! File::isDirectory($dest)) {
                File::copyDirectory("$default_dir$dname", $dest);
            }
        }

        $exclude = ['.', '..', ...$dir_names_to_copy];
        $files = array_diff(scandir($id_card_theme_dir), $exclude);
        array_walk_recursive($files, function (&$file) {
            $file = explode('.', $file)[0]; // strip .blade.php
        });

        return $files;
    }
}
