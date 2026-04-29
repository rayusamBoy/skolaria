<?php

namespace App\Helpers;

use App\Models\BloodGroup;
use App\Models\ClassType;
use App\Models\Lga;
use App\Models\MyClass;
use App\Models\Nationality;
use App\Models\StudentRecord;
use App\Models\Ticket;
use App\Repositories\UserRepo;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Usr extends Qs
{
    public static function getUsersByIds($ids, $with_trashed = false)
    {
        return $with_trashed ? User::withTrashed()->whereIn("id", $ids)->get() : User::whereIn("id", $ids)->get();
    }

    public static function getUsersByRoleNames(array $role_names, array $with = [])
    {
        $userRepo = new UserRepo();
        return $userRepo->getUsersByRoleNames($role_names, $with);
    }

    public static function getEducationLevels()
    {
        return ['Primary', 'Secondary', 'Advanced', 'Certificate', 'Diploma', 'Degree', 'Master', 'Doctoral', 'PhD'];
    }

    public static function getLgas()
    {
        return Lga::all();
    }

    public static function getStaffRoles()
    {
        return [
            'Master',
            'Mistress',
            'BASA',
            'Second Master',
            'Second Mistress',
            'Academic Master',
            'Academic Mistress',
            'Section Leader',
            'Class Teacher',
            'Ordinary Teacher',
            'Part-time Teacher',
            'Volunteer Teacher',
            'Guest Teacher',
            'Statistician',
            'Cashier',
            'Counselor',
            'Registrar',
            'Security Guard',
            'Cook',
            'Waiter',
            'Librarian',
            'An IT person',
            'Patron',
            'Matron',
        ];
    }

    public static function getPredefinedSubjects()
    {
        $subjects = [
            'Qualifying Test',
            'Civics',
            'History',
            'Geography',
            'Bible Knowledge',
            'Elimu ya Dini ya Kiislamu',
            'Fine Art',
            'Music',
            'Physical Education',
            'Theatre Arts',
            'Kiswahili',
            'English Language',
            'French Language',
            'Literature in English',
            'Arabic Language',
            'Chinese Language',
            'Physics',
            'Chemistry',
            'Biology',
            'Agricultural Science',
            'Engineering Science',
            'Information and Computer Studies',
            'Basic Mathematics',
            'Additional Mathematics',
            'Food and Nutrition',
            'Textiles and Dressmaking',
            'Commerce',
            'Book-Keeping',
            'Building Construction',
            'Architectural Draughting',
            'Surveying',
            'Carpentry and Joinery',
            'Brickwork and Masonry',
            'Painting and Sign Writing',
            'Plumbing',
            'Electrical Engineering Science',
            'Radio and Television Servicing',
            'Electrical Draughting',
            'Refrigeration and Air Conditioning',
            'Plant and Equipment Maintenance',
            'Workshop Technology',
            'Motor Vehicle Mechanics',
            'Welding and Metal Fabrication',
            'Fitting and Turning',
            'Auto Electrics',
            'Mechanical Draughting',
            'Foundry and Blacksmith',
            'General Studies',
            'Divinity',
            'Islamic Knowledge',
            'Agriculture',
            'Computer Science',
            'Basic Applied Mathematics',
            'Advanced Mathematics',
            'Economics',
            'Accountancy',
            'Food and Human Nutrition'
        ];

        return $subjects;
    }

    public static function getStudentParentsStatus()
    {
        return [
            'An Orphan',
            'Not Orphan',
            'The Child of a single Mother',
        ];
    }

    public static function getDisabilities()
    {
        return [
            'People who are blind or who have partial vision' => 'Vision Impairment',
            'People who are deaf or hard of hearing' => 'Hearing Impairment',
            'People with illness that affect the mind or brain (the way a person thinks, feels, and acts). Includes, bipolar disorder, depression, schizophrenia, anxiety and personality disorder.' => 'Mental Illness',
            'A person that may have significant limitations in the skills needed to live and work in the community. Including, difficulties with communication, self-care, social skills, safety, and self-direction' => 'Intellectual Disability',
            'Refers to any type of brain damage that occurs after birth. The injury may occur because of infection, disease, lack of oxygen or trauma to the head.' => 'Acquired Brain Injury',
            'It is an umbrella description which includes Autistic disorder, Asperger\'s syndrome and atypical autism. It affects the way information is teken in and stored in the brain.' => 'Autism Spectrum Disorder',
            'That is some aspect of a person\'s pysical functioning, usually either their mobility, dexterity, or stamina, is affected' => 'Physical Disability',
        ];
    }

    public static function getAllClassesOrdered($order_by = "name")
    {
        return once(fn() => MyClass::with(['class_type', 'section'])->orderBy($order_by)->get());
    }

    public static function getClassTypes()
    {
        return ClassType::orderBy('id')->get();
    }

    public static function getStudents($data)
    {
        return once(fn() => StudentRecord::with('user')->where($data)->get()->whereNotNull('user'));
    }

    public static function getClassStudents($class_id)
    {
        return StudentRecord::where(['my_class_id' => $class_id])->with('user')->get()->whereNotNull('user');
    }

    public static function getStudentRecordByUserIds($ids, $relationship = [])
    {
        return StudentRecord::whereIn("user_id", $ids)->with($relationship)->get();
    }

    public static function getStudentRecordByUserId($id, $relationship = [])
    {
        return StudentRecord::where('user_id', $id)->with($relationship)->get();
    }

    // Students Classes
    public static function renderClasses(Collection $class_collection)
    {
        $collection = '';
        foreach ($class_collection as $class) {
            $collection .= '<th colspan="' . $class->section->count() . '" class="text-center">' . $class->name . '</th>';
        }
        return $collection;
    }

    // Class Sections
    public static function renderClassSections(Collection $class_collection)
    {
        $collection = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $collection .= "<td>{$section->name}</td>";
            }
        }
        return $collection;
    }

    // Total Male Students per Section ie., A, B, and C
    public static function renderMaleCountPerSection(Collection $class_collection)
    {
        $collection = $counts = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $counts .= '<td>' . self::getStudents(['my_class_id' => $class->id, 'section_id' => $section->id, 'grad' => 0])->where('user.gender', User::GENDER_MALE)->count() . '</td>';
            }
            $collection .= $counts;
            $counts = '';
        }
        return $collection;
    }

    // Total Female Students per Section ie., A, B, and C
    public static function renderFemaleCountPerSection(Collection $class_collection)
    {
        $collection = $counts = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $counts .= '<td>' . self::getStudents(['my_class_id' => $class->id, 'section_id' => $section->id, 'grad' => 0])->where('user.gender', User::GENDER_FEMALE)->count() . '</td>';
            }
            $collection .= $counts;
            $counts = '';
        }
        return $collection;
    }

    // Total Students (Male and Female) per Section
    public static function renderTotalPerSection(Collection $class_collection)
    {
        $collection = $counts = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $counts .= '<td>' . self::getStudents(['my_class_id' => $class->id, 'section_id' => $section->id, 'grad' => 0])->count() . '</td>';
            }
            $collection .= $counts;
            $counts = '';
        }
        return $collection;
    }

    // Total Students (Male and Female) per Class
    public static function renderTotalPerClass(Collection $class_collection)
    {
        $counts = '';
        foreach ($class_collection as $class) {
            $counts .= '<td colspan="' . $class->section->count() . '">' . self::getStudents(['my_class_id' => $class->id, 'grad' => 0])->count() . '</td>';
        }
        return $counts;
    }

    public static function renderTable($class_collection)
    {
        if ($class_collection->count() <= 0)
            return 'No record found.';

        return
            '<table class="table table-sm table-responsive text-center">
                <thead>
                    <tr><th>Class</th>' . self::renderClasses($class_collection) . ' </tr>
                    <tr><th>Section</th>' . self::renderClassSections($class_collection) . '</tr>
                </thead>
                <tbody>
                    <tr><td>Male</td>' . self::renderMaleCountPerSection($class_collection) . '</tr>
                    <tr><td>Female</td>' . self::renderFemaleCountPerSection($class_collection) . '</tr>
                </tbody>
                <tfoot>
                    <tr><td>Total per Section</td>' . self::renderTotalPerSection($class_collection) . '</tr>
                    <tr><td>Total per Class</td>' . self::renderTotalPerClass($class_collection) . ' </tr>
                </tfoot>
            </table>';
    }

    public static function getNationality($nal_id)
    {
        return Nationality::where('id', $nal_id)->get();
    }

    public static function createAvatar($name, $code, $user_role)
    {
        $words = explode(" ", $name);
        // Assume the names parts (first, middle, surname) are separated by a space.
        // Take the first letter of the name parts in upper case.
        $initials = strtoupper(substr($words[0], 0, 1) . (\array_key_exists(1, $words) && $words[1] != "" ? substr($words[1], 0, 1) : (\array_key_exists(2, $words) && $words[2] != "" ? substr($words[2], 0, 1) : substr($words[0], 1, 1))));

        // Define a background color and text color for the avatar
        $textColor = '#' . substr(md5($name), 0, 6); // Use a unique text color based on the name
        // $bgColor = '#ffffff'; // White background color

        // Create an image with the initials and colors
        $image = imagecreate(200, 200);

        // $bg = imagecolorallocate($image, hexdec(substr($bgColor, 1, 2)), hexdec(substr($bgColor, 3, 2)), hexdec(substr($bgColor, 5, 2))); // White background color manipulation
        $bg = imagecolorallocatealpha($image, 0, 0, 0, 127); // Tranparent background
        $text = imagecolorallocate($image, hexdec(substr($textColor, 1, 2)), hexdec(substr($textColor, 3, 2)), hexdec(substr($textColor, 5, 2)));

        imagefill($image, 0, 0, $bg);
        imagettftext($image, 60, 0, 40, 130, $text, public_path('global_assets/fonts/CASTELAR.ttf'), $initials);

        // Try to create directory first if does not exists
        $dir_for_db = Qs::getUploadPath($user_role) . $code;
        $dir_for_store = storage_path() . '/app/public/' . Qs::getUploadPath($user_role) . $code;

        if (!is_dir($dir_for_store))
            mkdir($dir_for_store, 0755, true);

        // Save the image to a file
        $avatar_path = "$dir_for_db/photo.png";
        $img_path = "$dir_for_store/photo.png";

        imagepng($image, $img_path);
        imagedestroy($image);

        return $avatar_path;
    }

    public static function getBloodGroups()
    {
        return BloodGroup::all();
    }

    public static function getBookStatuses(): array
    {
        return [
            'Due' => 'This means that the item is being read by another and is due to be returned on the date listed.',
            'In Library' => 'This means that the item is available for a user to borrow.',
            'Lib Use Only' => 'This means that the item may only be used within the library.',
            'Not Available' => 'This means that the item is temporary not available due to special circumstances.',
            'Missing' => 'This means that the item has been reported missing and/or is being searched for by library staff.',
            'Lost' => 'This means that the item it has officially reported as lost and/or is being evaluated for replacement.',
            'Damaged' => 'This means that the item is damaged and/or is being evaluated for replacement.',
            'Billed' => 'This means that the item is not available that is it was not returned and the borrower has been billed for the replacement cost.',
        ];
    }

    public static function tenancyInitialized(): bool
    {
        return tenancy()->initialized;
    }

    public static function getAccountStatuses(array $remove = []): array
    {
        $statuses = ['Active', 'Inactive', 'Suspended', 'Blocked'];

        return $remove ? array_values(array_diff($statuses, $remove)) : $statuses;
    }

    /**
     * Get tenancy-aware asset URL for a photo with cache busting.
     * Appends file modification timestamp as query parameter for Workbox caching.
     *
     * @param string $url Relative URL of the photo asset (e.g., "uploads/avatars/photo.jpg")
     * @return string
     */
    public static function getTenancyAwarePhoto(string $url): string
    {
        $base_url = self::tenancyInitialized()
            ? (str_contains($url, 'global_assets') ? asset($url) : tenant_asset($url))
            : asset($url);

        $is_tenant_asset = self::tenancyInitialized() && !str_contains($url, 'global_assets');
        $cache_key = $is_tenant_asset
            ? "asset_version_tenant_" . tenant('id') . "_{$url}"
            : "asset_version_global_{$url}";

        $ttl = 60 * 10; // 10 minutes
        $version = cache()->remember($cache_key, $ttl, function () use ($url, $is_tenant_asset) {
            $file_path = ($is_tenant_asset) ? Storage::disk('public')->path($url) : public_path(ltrim($url, '/'));

            return file_exists($file_path) ? filemtime($file_path) : config('app.version', '1.0.0');
        });

        return "$base_url?$version";
    }

    public static function getStudentExamNumberPlaceholder()
    {
        return "*";
    }

    public static function getGenders()
    {
        return [User::GENDER_MALE, User::GENDER_FEMALE];
    }

    public static function getReligions()
    {
        return ['Islam', 'Christianity', 'Other'];
    }

    public static function getDateFormat()
    {
        return 'm/d/Y';
    }

    public static function userAssignedTickets($user_id = null)
    {
        $user_id ??= auth()->id();
        return once(fn() => Ticket::where('assigned_to', $user_id)->where('status', '!=', 'closed')->where('is_archived', false)->get());
    }

    public static function getUserMediaHeadingColor($name)
    {
        return '#' . substr(md5($name), 0, 6);
    }

    public static function getTenancyAwareRootRoles(): array
    {
        return array_values(
            config(
                self::tenancyInitialized()
                ? 'roles.root_roles.tenant'
                : 'roles.root_roles.central',
                []
            )
        );
    }

    public static function getTenancyAwareDefaultRoles()
    {
        return array_values(
            config(
                self::tenancyInitialized()
                ? 'roles.default_roles.tenant'
                : 'roles.default_roles.central',
                []
            )
        );
    }

    public static function getTenancyAwareStaffRoles()
    {
        return array_values(
            config(
                self::tenancyInitialized()
                ? 'roles.staff_roles.tenant'
                : 'roles.staff_roles.central',
                []
            )
        );
    }

    public static function deleteUserPhotoDirs(collection|array $role_names, string $code)
    {
        foreach ($role_names as $name) {
            $path = self::getUploadPath(strtolower($name)) . $code;
            Storage::exists($path) ? Storage::deleteDirectory($path) : false;
        }
    }

    public static function getAvailableAppSkins()
    {
        return [
            'default' => 'Original light/dark balanced theme',
            'teal' => 'Modern teal with a fresh, tech‑friendly feel',
            'slate' => 'Professional slate theme with balanced neutrals and blue accents',
            'navy' => 'Professional deep blue with sage green accents',
            'green' => 'Calm, natural green inspired by growth and learning',
            'warm' => 'Friendly slate blue with warm orange highlights',
            'combined' => 'Harmonious blend of teal, navy, and green',
            'highcontrast' => 'Maximum readability for low‑vision users',
            'invert' => 'Simple inversion of the default theme',
            'grayscale' => 'Distraction‑free shades of gray',
            'slateglass' => 'Professional slate theme with transparent elements',
        ];
    }

    public static function getDefaultUserPreferences()
    {
        $preferences = [
            'always_minimize_sidebar' => 0,
            'allow_system_sounds' => 1,
            'active_system_skin' => array_key_first(self::getAvailableAppSkins()),
        ];

        return $preferences;
    }

    public static function getDefaultIsNotifiableUserColumnValues()
    {
        $values = [
            'status' => (bool) true,
            'on_email_channel' => (bool) true,
            'on_sms_channel' => (bool) true,
            'on_push_channel' => (bool) true,
        ];

        return $values;
    }

    public static function getLoginAndRelatedPagesBgDescription()
    {
        return 'storage/uploads/login_and_related_pages_bg.png';
    }

    public static function getTenancyAwareImgOrDefaultBgUrl($img)
    {
        if ($img === null)
            return asset(path: self::getLoginAndRelatedPagesBgDescription());

        $img_desc = $img->description;
        $img_url = $img_desc === null ? asset(path: self::getLoginAndRelatedPagesBgDescription()) : (self::tenancyInitialized() ? tenant_asset($img_desc) : asset($img_desc));
        $img_url = "$img_url?" . strtotime($img->updated_at);

        return $img_url;
    }
}
