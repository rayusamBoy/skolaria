<?php

namespace App\Helpers;

use App\Models\BloodGroup;
use App\Models\ClassType;
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
    /**
     * Get users by IDs, optionally including soft‑deleted ones.
     */
    public static function getUsersByIds(array $ids, bool $with_trashed = false): Collection
    {
        return $with_trashed
            ? User::withTrashed()->whereIn('id', $ids)->get()
            : User::whereIn('id', $ids)->get();
    }

    /**
     * Get users by role names.
     */
    public static function getUsersByRoleNames(array $role_names, array $with = []): Collection
    {
        $userRepo = new UserRepo;

        return $userRepo->getUsersByRoleNames($role_names, $with);
    }

    /**
     * List of education levels.
     */
    public static function getEducationLevels(): array
    {
        return ['Primary', 'Secondary', 'Advanced', 'Certificate', 'Diploma', 'Degree', 'Master', 'Doctoral', 'PhD'];
    }

    /**
     * List of staff designations.
     */
    public static function getStaffDesignations(): array
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
            'Part‑time Teacher',
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

    /**
     * List of predefined subject names.
     */
    public static function getPredefinedSubjects(): array
    {
        return [
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
            'Book‑Keeping',
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
            'Food and Human Nutrition',
        ];
    }

    /**
     * Statuses for student parents.
     */
    public static function getStudentParentsStatus(): array
    {
        return [
            'An Orphan',
            'Not Orphan',
            'The Child of a single Mother',
        ];
    }

    /**
     * Disability types (values) with descriptions (keys).
     */
    public static function getDisabilities(): array
    {
        return [
            'People who are blind or who have partial vision' => 'Vision Impairment',
            'People who are deaf or hard of hearing' => 'Hearing Impairment',
            'People with illness that affect the mind or brain (the way a person thinks, feels, and acts). Includes, bipolar disorder, depression, schizophrenia, anxiety and personality disorder.' => 'Mental Illness',
            'A person that may have significant limitations in the skills needed to live and work in the community. Including, difficulties with communication, self-care, social skills, safety, and self‑direction' => 'Intellectual Disability',
            'Refers to any type of brain damage that occurs after birth. The injury may occur because of infection, disease, lack of oxygen or trauma to the head.' => 'Acquired Brain Injury',
            'It is an umbrella description which includes Autistic disorder, Asperger\'s syndrome and atypical autism. It affects the way information is taken in and stored in the brain.' => 'Autism Spectrum Disorder',
            'That is some aspect of a person\'s physical functioning, usually either their mobility, dexterity, or stamina, is affected' => 'Physical Disability',
        ];
    }

    /**
     * Get all classes ordered by a given column (cached).
     */
    public static function getAllClassesOrdered(string $order_by = 'name'): Collection
    {
        return once(fn () => MyClass::with(['class_type', 'section'])->orderBy($order_by)->get());
    }

    /**
     * Get all class types ordered by ID.
     */
    public static function getClassTypes(): Collection
    {
        return ClassType::orderBy('id')->get();
    }

    /**
     * Get students matching given conditions, including user relation (cached once).
     */
    public static function getStudentsWhere(array $where): Collection
    {
        return once(fn () => StudentRecord::with('user')->where($where)->get()->whereNotNull('user'));
    }

    /**
     * Get all students of a class (with user relation).
     */
    public static function getClassStudents(int $class_id): Collection
    {
        return StudentRecord::where('my_class_id', $class_id)->with('user')->get()->whereNotNull('user');
    }

    /**
     * Get student records by user IDs with optional relationships.
     */
    public static function getStudentRecordByUserIds(array $ids, array $relationship = []): Collection
    {
        return StudentRecord::whereIn('user_id', $ids)->with($relationship)->get();
    }

    /**
     * Get student records for a single user ID with optional relationships.
     */
    public static function getStudentRecordByUserId(int $id, array $relationship = []): Collection
    {
        return StudentRecord::where('user_id', $id)->with($relationship)->get();
    }

    /**
     * Render class names as table headers, spanning their sections.
     */
    public static function renderClassHeaders(Collection $class_collection): string
    {
        $html = '';
        foreach ($class_collection as $class) {
            $html .= '<th colspan="'.$class->section->count().'" class="text-center">'.$class->name.'</th>';
        }

        return $html;
    }

    /**
     * Render section names as table cells.
     */
    public static function renderSectionCells(Collection $class_collection): string
    {
        $html = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $html .= "<td>{$section->name}</td>";
            }
        }

        return $html;
    }

    /**
     * Render male student count per section (active students).
     */
    public static function renderMaleCountPerSectionRow(Collection $class_collection): string
    {
        $html = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $count = self::getStudentsWhere([
                    'my_class_id' => $class->id,
                    'section_id' => $section->id,
                    'is_graduated' => false,
                ])->where('user.gender', User::GENDER_MALE)->count();
                $html .= "<td>$count</td>";
            }
        }

        return $html;
    }

    /**
     * Render female student count per section (active students).
     */
    public static function renderFemaleCountPerSectionRow(Collection $class_collection): string
    {
        $html = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $count = self::getStudentsWhere([
                    'my_class_id' => $class->id,
                    'section_id' => $section->id,
                    'is_graduated' => false,
                ])->where('user.gender', User::GENDER_FEMALE)->count();
                $html .= "<td>$count</td>";
            }
        }

        return $html;
    }

    /**
     * Render total student count per section (active).
     */
    public static function renderTotalPerSectionRow(Collection $class_collection): string
    {
        $html = '';
        foreach ($class_collection as $class) {
            foreach ($class->section as $section) {
                $count = self::getStudentsWhere([
                    'my_class_id' => $class->id,
                    'section_id' => $section->id,
                    'is_graduated' => false,
                ])->count();
                $html .= "<td>$count</td>";
            }
        }

        return $html;
    }

    /**
     * Render total student count per class (active).
     */
    public static function renderTotalPerClassCell(Collection $class_collection): string
    {
        $html = '';
        foreach ($class_collection as $class) {
            $count = self::getStudentsWhere(['my_class_id' => $class->id, 'is_graduated' => false])->count();
            $html .= '<td colspan="'.$class->section->count().'">'.$count.'</td>';
        }

        return $html;
    }

    /**
     * Render a complete class/section/student breakdown table.
     */
    public static function renderClassPopulationTable(Collection $class_collection): string
    {
        if ($class_collection->count() <= 0) {
            return 'No record found.';
        }

        return '<table class="table table-sm table-responsive text-center">
            <thead>
                <tr><th>Class</th>'.self::renderClassHeaders($class_collection).'</tr>
                <tr><th>Section</th>'.self::renderSectionCells($class_collection).'</tr>
            </thead>
            <tbody>
                <tr><td>Male</td>'.self::renderMaleCountPerSectionRow($class_collection).'</tr>
                <tr><td>Female</td>'.self::renderFemaleCountPerSectionRow($class_collection).'</tr>
            </tbody>
            <tfoot>
                <tr><td>Total per Section</td>'.self::renderTotalPerSectionRow($class_collection).'</tr>
                <tr><td>Total per Class</td>'.self::renderTotalPerClassCell($class_collection).'</tr>
            </tfoot>
        </table>';
    }

    /**
     * Get nationality by ID.
     */
    public static function getNationality(int $nationality_id): Collection
    {
        return Nationality::where('id', $nationality_id)->get();
    }

    /**
     * Generate an avatar image for a user.
     */
    public static function createAvatar(string $name, string $code, string $user_role): string
    {
        $words = explode(' ', $name);
        $initials = strtoupper(
            substr($words[0], 0, 1)
            .(isset($words[1]) && $words[1] !== ''
                ? substr($words[1], 0, 1)
                : (isset($words[2]) && $words[2] !== ''
                    ? substr($words[2], 0, 1)
                    : substr($words[0], 1, 1)
                )
            )
        );

        $textColor = '#'.substr(md5($name), 0, 6);
        $image = imagecreate(200, 200);
        $bg = imagecolorallocatealpha($image, 0, 0, 0, 127);
        $text = imagecolorallocate(
            $image,
            hexdec(substr($textColor, 1, 2)),
            hexdec(substr($textColor, 3, 2)),
            hexdec(substr($textColor, 5, 2))
        );
        imagefill($image, 0, 0, $bg);
        imagettftext($image, 60, 0, 40, 130, $text, public_path('global_assets/fonts/CASTELAR.ttf'), $initials);

        $dir_for_db = Qs::getUploadPath($user_role).$code;
        $dir_for_store = storage_path().'/app/public/'.$dir_for_db;
        if (! is_dir($dir_for_store)) {
            mkdir($dir_for_store, 0755, true);
        }

        $avatar_path = "$dir_for_db/photo.png";
        $img_path = "$dir_for_store/photo.png";
        imagepng($image, $img_path);
        imagedestroy($image);

        return $avatar_path;
    }

    /**
     * Get all blood groups.
     */
    public static function getBloodGroups(): Collection
    {
        return BloodGroup::all();
    }

    /**
     * Book statuses with explanations.
     */
    public static function getBookStatuses(): array
    {
        return [
            'In Library' => 'This means that the item is available for a user to borrow.',
            'Due' => 'This means that the item is being read by another and is due to be returned on the date listed.',
            'Lib Use Only' => 'This means that the item may only be used within the library.',
            'Not Available' => 'This means that the item is temporary not available due to special circumstances.',
            'Missing' => 'This means that the item has been reported missing and/or is being searched for by library staff.',
            'Lost' => 'This means that the item it has officially reported as lost and/or is being evaluated for replacement.',
            'Damaged' => 'This means that the item is damaged and/or is being evaluated for replacement.',
            'Billed' => 'This means that the item is not available that is it was not returned and the borrower has been billed for the replacement cost.',
        ];
    }

    /**
     * Check if tenancy is initialized.
     */
    public static function tenancyInitialized(): bool
    {
        return tenancy()->initialized;
    }

    /**
     * Account statuses, optionally excluding some.
     */
    public static function getAccountStatuses(array $remove = []): array
    {
        $statuses = ['Active', 'Inactive', 'Suspended', 'Blocked'];

        return $remove ? array_values(array_diff($statuses, $remove)) : $statuses;
    }

    /**
     * Get the default user image. Used when a user does not have a photo.
     *
     * @return string
     */
    public static function getDefaultUserImage()
    {
        return 'global_assets/images/user.png';
    }

    /**
     * Get tenancy‑aware asset URL for a photo with cache busting.
     *
     * @param  string|null  $url  Relative URL of the photo asset (e.g., "path/to/photo"). Defaults to the default user image.
     */
    public static function getTenancyAwarePhotoUrl(?string $url): string
    {
        $url ??= self::getDefaultUserImage();
        $base_url = self::tenancyInitialized()
            ? (str_contains($url, 'global_assets') ? asset($url) : tenant_asset($url))
            : asset($url);

        $is_tenant_asset = self::tenancyInitialized() && ! str_contains($url, 'global_assets');
        $cache_key = $is_tenant_asset
            ? 'asset_version_tenant_'.tenant('id')."_{$url}"
            : "asset_version_global_{$url}";

        $ttl = 60 * 10; // 10 minutes
        $version = cache()->remember($cache_key, $ttl, function () use ($url, $is_tenant_asset) {
            $file_path = $is_tenant_asset
                ? Storage::disk('public')->path($url)
                : public_path(ltrim($url, '/'));

            return file_exists($file_path) ? filemtime($file_path) : config('app.version', '1.0.0');
        });

        return "$base_url?$version";
    }

    /**
     * Placeholder used for student exam number generation.
     */
    public static function getStudentExamNumberPlaceholder(): string
    {
        return '*';
    }

    /**
     * Get the two genders.
     */
    public static function getGenders(): array
    {
        return [User::GENDER_MALE, User::GENDER_FEMALE];
    }

    /**
     * Get list of religions.
     */
    public static function getReligions(): array
    {
        return ['Islam', 'Christianity', 'Other'];
    }

    /**
     * Get the application date format.
     */
    public static function getDateFormat(): string
    {
        return 'Y-m-d';
    }

    /**
     * Get tickets assigned to a user that are not closed or archived (cached).
     */
    public static function userAssignedTickets(?int $user_id = null): Collection
    {
        $user_id ??= auth()->id();

        return once(fn () => Ticket::where('assigned_to', $user_id)
            ->where('status', '!=', 'closed')
            ->where('is_archived', false)
            ->get());
    }

    /**
     * Generate a unique heading color based on a name.
     */
    public static function getUserMediaHeadingColor(string $name): string
    {
        return '#'.substr(md5($name), 0, 6);
    }

    /**
     * Root roles for the current tenancy context.
     */
    public static function getTenancyAwareRootRoles(): array
    {
        return array_values(config(
            self::tenancyInitialized()
            ? 'roles.root_roles.tenant'
            : 'roles.root_roles.central',
            []
        ));
    }

    /**
     * Default roles for the current tenancy context.
     */
    public static function getTenancyAwareDefaultRoles(): array
    {
        return array_values(config(
            self::tenancyInitialized()
            ? 'roles.default_roles.tenant'
            : 'roles.default_roles.central',
            []
        ));
    }

    /**
     * Staff roles for the current tenancy context.
     */
    public static function getTenancyAwareStaffRoles(): array
    {
        return array_values(config(
            self::tenancyInitialized()
            ? 'roles.staff_roles.tenant'
            : 'roles.staff_roles.central',
            []
        ));
    }

    /**
     * Delete user photo directories for the given roles and user code.
     */
    public static function deleteUserPhotoDirs(Collection|array $role_names, string $code): void
    {
        foreach ($role_names as $name) {
            $path = self::getUploadPath(strtolower($name)).$code;
            if (Storage::exists($path)) {
                Storage::deleteDirectory($path);
            }
        }
    }

    /**
     * Get the available application skins/themes with short description.
     */
    public static function getAvailableAppSkins(): array
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

    /**
     * Default user preferences.
     */
    public static function getDefaultUserPreferences(): array
    {
        return [
            'always_minimize_sidebar' => 0,
            'allow_system_sounds' => 1,
            'allow_offline_access' => 0,
            'active_system_skin' => array_key_first(self::getAvailableAppSkins()),
        ];
    }

    /**
     * Default notification preference values for a newly created user.
     */
    public static function getDefaultNotificationPreferences(): array
    {
        return [
            'status' => true,
            'on_email_channel' => true,
            'on_sms_channel' => true,
            'on_push_channel' => true,
        ];
    }

    /**
     * Get the value (path) of the login/related pages background image.
     */
    public static function getLoginAndRelatedPagesBgValue(): string
    {
        return 'storage/uploads/login_and_related_pages_bg.svg';
    }

    /**
     * Get a tenancy‑aware image URL (or the default background) with cache busting.
     *
     * @param  mixed  $img  must have value and updated_at properties.
     */
    public static function getTenancyAwareImgOrDefaultBgUrl($img): string
    {
        $img_desc = $img?->value;
        $img_url = $img_desc === null
            ? asset(path: self::getLoginAndRelatedPagesBgValue())
            : (self::tenancyInitialized() ? tenant_asset($img_desc) : asset($img_desc));

        return "$img_url?".strtotime($img->updated_at);
    }
}
