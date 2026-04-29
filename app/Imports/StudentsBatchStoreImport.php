<?php

namespace App\Imports;

use App\Helpers\Usr;
use App\Http\Controllers\StudentRecordController;
use App\Http\Requests\Student\StudentRecordCreate;
use App\Repositories\DormRepo;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class StudentsBatchStoreImport extends DefaultValueBinder implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, WithCustomValueBinder
{
    protected LocationRepo $location;
    protected DormRepo $dorm;
    protected MyClassRepo $my_class;
    protected UserRepo $user;
    protected StudentRepo $student;
    protected StudentRecordCreate $student_record_request;
    protected string $session;
    protected array $user_parameters;
    protected array $student_parameters;
    protected array $all_parameters;
    protected array $parameters_columns_to_hide_and_populate;
    protected array $date_columns;
    protected array $rules_cache;

    public function __construct()
    {
        $this->session = Usr::getCurrentSession();
        $this->my_class = new MyClassRepo();
        $this->user = new UserRepo();
        $this->student = new StudentRepo();
        $this->location = new LocationRepo();
        $this->dorm = new DormRepo();
        $this->student_record_request = new StudentRecordCreate();

        $this->user_parameters = StudentRecordController::getUserHeadings(false);
        $this->student_parameters = StudentRecordController::getStudentHeadings(false);
        $this->all_parameters = [...$this->user_parameters, ...$this->student_parameters];
        $this->parameters_columns_to_hide_and_populate = ['state_id', 'nal_id', 'my_class_id'];

        // Precompute date columns (letters) for bindValue
        $this->date_columns = $this->buildDateColumns();
        $this->rules_cache = $this->student_record_request->rules();
    }

    /**
     * Convert Excel date values to PHP dates for date columns.
     */
    public function bindValue(Cell $cell, $value)
    {
        if (!empty($value) && \in_array($cell->getColumn(), $this->date_columns)) {
            $cell->setValueExplicit(
                Date::excelToDateTimeObject($value)->format(Usr::getDateFormat()),
                DataType::TYPE_STRING
            );
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * Determine whether a row should be skipped.
     */
    public function isEmptyWhen(array $row): bool
    {
        // 1. Is it the heading row (value contains the heading name itself)?
        foreach ($this->all_parameters as $attribute) {
            if (str_contains($row[$attribute] ?? '', $attribute)) {
                return true;
            }
            break; // Only check the first attribute (name)
        }

        // 2. Does the row contain only auto‑populated hidden parameters?
        $non_null = array_keys(array_filter($row, fn($value) => $value != null));
        return \count(array_diff($non_null, $this->parameters_columns_to_hide_and_populate)) === 0;
    }

    /**
     * Process the imported rows and create student records.
     */
    public function collection(Collection $rows): void
    {
        $first = $rows->first();
        $my_class_id = $first['my_class_id'];
        $state_id = $first['state_id'];

        $blood_groups = Usr::getBloodGroups();
        $lgas = $this->location->getLgas($state_id);
        $sections = $this->my_class->getClassSections($my_class_id);
        $dorms = $this->dorm->getAll();

        foreach ($rows as $row) {
            $row['bg_id'] = $blood_groups->where('name', $row['bg_id'])->value('id');
            $row['lga_id'] = $lgas->where('name', $row['lga_id'])->value('id');
            $row['section_id'] = $sections->where('name', $row['section_id'])->value('id');
            $row['dorm_id'] = $dorms->where('name', $row['dorm_id'])->value('id');

            $user_data = $this->extractUserData($row);
            $student_data = $this->extractStudentData($row, $user_data['username']);

            $user = $this->user->create($user_data);
            $user->assignRole(config('roles.default_roles.tenant.student'));

            $student_data['user_id'] = $user->id;
            $this->student->createRecord($student_data);
        }
    }

    /**
     * Validation attribute names.
     */
    public function customValidationAttributes(): array
    {
        return array_flip($this->all_parameters);
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return $this->rules_cache;
    }

    /**
     * Heading row index (0‑based, Excel row 4).
     */
    public function headingRow(): int
    {
        return 4;
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Extract user‑related data from a row.
     */
    protected function extractUserData(array $row): array
    {
        $data = [];
        foreach ($this->user_parameters as $param) {
            $data[$param] = $row[$param];
        }

        $name = strtoupper($row['name']);
        $code = strtoupper(Str::random(10));
        $user_role = config('roles.default_roles.tenant.student');

        $data['user_role'] = $user_role;
        $data['name'] = $name;
        $data['code'] = $code;
        $data['password'] = Hash::make('student');
        $data['photo'] = Usr::createAvatar($name, $code, $user_role);

        $ct = $this->my_class->findTypeByClass($row['my_class_id'])->code;
        $adm_no = $row['adm_no'];
        $data['username'] = strtoupper(
            Usr::getAppCode() . '/' . $ct . '/' . date('Y', strtotime($row['date_admitted'])) . '/' . ($adm_no ?: mt_rand(1000, 99999))
        );

        return $data;
    }

    /**
     * Extract student‑related data from a row.
     */
    protected function extractStudentData(array $row, string $username): array
    {
        $sr = [];
        foreach ($this->student_parameters as $param) {
            $sr[$param] = $row[$param];
        }

        $sr['adm_no'] = $username;
        $sr['session'] = $this->session;
        $sr['house_no'] = strtoupper($sr['house_no'] ?? '');
        $sr['ps_name'] = ucfirst($sr['ps_name'] ?? '');
        $sr['ss_name'] = ucfirst($sr['ss_name'] ?? '');
        $sr['p_status'] = ucfirst($sr['p_status'] ?? '');

        return $sr;
    }

    /**
     * Build the list of column letters that contain dates.
     */
    protected function buildDateColumns(): array
    {
        $date_params = array_keys(array_filter(
            $this->rules_cache,
            fn($rule) =>
            \is_array($rule) ? \in_array('date', $rule) || \in_array('date_format', $rule) : str_contains($rule, 'date')
        ));

        return array_map(fn($param) => $this->mapHeadingToLetter($param), $date_params);
    }

    /**
     * Map a heading name to its Excel column letter.
     */
    private function mapHeadingToLetter(string $heading_param): string
    {
        $all = array_values($this->all_parameters);
        $index = array_search($heading_param, $all);
        return Usr::numberToLetter(($index !== false ? $index : 0) + 1);
    }
}
