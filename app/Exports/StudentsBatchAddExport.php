<?php

namespace App\Exports;

use App\Helpers\Usr;
use App\Http\Controllers\StudentRecordController;
use App\Http\Requests\Student\StudentRecordCreate;
use App\Repositories\DormRepo;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use Dompdf\Css\Color;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsBatchAddExport implements FromView, ShouldAutoSize, WithColumnWidths, WithEvents, WithProperties, WithStyles
{
    protected array $data;

    protected LocationRepo $location;

    protected MyClassRepo $my_class;

    protected DormRepo $dorm;

    protected array $all_headings;

    protected StudentRecordCreate $student_record_request;

    protected array $parameters_columns_to_hide_and_populate;

    protected int $end_data_row;

    protected const int NUMBER_OF_HEADING_ROWS = 5;

    protected const int DEFAULT_END_ROW = 1000;

    protected const string SHEET_PASSWORD = 'AllahKnowsBest';

    public function __construct($data)
    {
        $this->data = $data;
        $this->location = new LocationRepo;
        $this->my_class = new MyClassRepo;
        $this->dorm = new DormRepo;
        $this->student_record_request = new StudentRecordCreate;

        $user_headings = StudentRecordController::getUserHeadings();
        $student_headings = StudentRecordController::getStudentHeadings();
        $this->all_headings = [...$user_headings, ...$student_headings];
        $this->parameters_columns_to_hide_and_populate = ['state_id', 'nationality_id', 'my_class_id'];
        $this->end_data_row = self::DEFAULT_END_ROW;
    }

    /**
     * Column widths for specific headings.
     */
    public function columnWidths(): array
    {
        $width_map = [
            'name' => 35,
            'email' => 25,
            'address' => 25,
            'food_taboos' => 45,
            'chronic_health_problems' => 45,
        ];

        $column_widths = [];
        foreach ($this->all_headings as $heading) {
            $lower = strtolower($heading);
            if (isset($width_map[$lower])) {
                $column_widths[$this->mapHeadingToLetter($heading)] = $width_map[$lower];
            }
        }

        return $column_widths;
    }

    /**
     * Document properties.
     */
    public function properties(): array
    {
        $system = Usr::getSystemName();

        return [
            'creator' => $system,
            'lastModifiedBy' => $system,
            'title' => 'Students Add Export',
            'description' => 'Excel template for adding students',
            'subject' => 'Students Add Export',
            'keywords' => 'students,export,spreadsheet',
            'category' => 'Students',
        ];
    }

    /**
     * Apply styles, data validations and protection to the worksheet.
     */
    public function styles(Worksheet $sheet): void
    {
        $start_data_row = self::NUMBER_OF_HEADING_ROWS + 1;
        $end_row = $this->end_data_row + self::NUMBER_OF_HEADING_ROWS;
        $end_col_index = \count($this->all_headings);

        // Apply dropdown validations
        $this->applyDropdownValidations($sheet, $start_data_row, $end_row);

        // Apply date validations
        $this->applyDateValidations($sheet, $start_data_row, $end_row);

        // Protect sheet and unlock data cells
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword(self::SHEET_PASSWORD);
        $sheet->getStyle([1, $start_data_row, $end_col_index, $end_row])
            ->getProtection()
            ->setLocked(Protection::PROTECTION_UNPROTECTED);

        // Header colors
        $this->applyHeaderColors($sheet, $end_col_index);
    }

    /**
     * Register events (after sheet creation).
     */
    public function registerEvents(): array
    {
        $params = $this->parameters_columns_to_hide_and_populate;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($params) {
                $sheet = $event->sheet->getDelegate();

                // Hide row 4
                $sheet->getRowDimension(4)->setVisible(false);
                // Freeze pane
                $sheet->freezePane('A6');

                // Hide specified columns
                foreach ($params as $param) {
                    $col = $this->mapHeadingToLetter($param);
                    $sheet->getColumnDimension($col)->setVisible(false);
                }

                // Apply date format to date columns
                $date_columns = $this->buildDateColumns();
                $last_row = $sheet->getHighestRow();
                $start_row = self::NUMBER_OF_HEADING_ROWS + 1;
                $format = str_replace('Y', 'yyyy', Usr::getDateFormat());

                foreach ($date_columns as $heading) {
                    $col = $this->mapHeadingToLetter($heading);
                    $sheet->getStyle("{$col}{$start_row}:{$col}{$last_row}")
                        ->getNumberFormat()
                        ->setFormatCode($format);
                }
            },
        ];
    }

    /**
     * Return the view for the export.
     */
    public function view(): View
    {
        $rules = $this->student_record_request->rules();
        $required_params = array_keys(array_filter(
            $rules,
            fn ($rule) => \is_array($rule) ? \in_array('required', $rule) : str_contains($rule, 'required')
        ));

        $this->data['parameters_with_rule_required'] = $required_params;
        $this->data['parameters_columns_to_hide_and_populate'] = $this->parameters_columns_to_hide_and_populate;
        $this->data['end_data_row'] = $this->end_data_row;

        return view('pages.students.batch.table', $this->data);
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Apply dropdown list validations for predefined columns.
     */
    protected function applyDropdownValidations(Worksheet $sheet, int $start_row, int $end_row): void
    {
        $mappings = $this->getDropdownMappings();

        foreach ($mappings as $heading => $options_callback) {
            $col_index = array_search($heading, $this->all_headings);
            if ($col_index === false) {
                continue;
            }
            $col_index += 1;

            $options = $options_callback();
            if (empty($options)) {
                continue;
            }

            $col_letter = Usr::numberToColumnLetter($col_index);
            $range = "{$col_letter}{$start_row}:{$col_letter}{$end_row}";

            $validation = $sheet->getCell($col_letter.$start_row + 1)->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Select from list.');
            $validation->setPrompt('Please, select a value from the drop-down list.');
            $validation->setFormula1(\sprintf('"%s"', implode(',', $options)));

            $sheet->setDataValidation($range, clone $validation);
        }
    }

    /**
     * Build the list of column names that contain dates.
     */
    protected function buildDateColumns(): array
    {
        return array_keys(array_filter(
            $this->student_record_request->rules(),
            fn ($rule) => \is_array($rule) ? \in_array('date', $rule) || \in_array('date_format', $rule) : str_contains($rule, 'date')
        ));
    }

    /**
     * Apply date validations for date columns.
     */
    protected function applyDateValidations(Worksheet $sheet, int $start_row, int $end_row): void
    {
        $date_columns = $this->buildDateColumns();

        foreach ($date_columns as $heading) {
            $col_index = array_search($heading, $this->all_headings);
            if ($col_index === false) {
                continue;
            }
            $col_index += 1;

            $col_letter = Usr::numberToColumnLetter($col_index);
            $range = "{$col_letter}{$start_row}:{$col_letter}{$end_row}";

            $validation = $sheet->getCell($col_letter.$start_row + 1)->getDataValidation();
            $validation->setType(DataValidation::TYPE_DATE);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not a valid date.');
            $validation->setPromptTitle('Enter a date.');
            $validation->setPrompt("Please, enter a valid date.\nDate format: ".str_replace('Y', 'yyyy', Usr::getDateFormat()));
            $validation->setFormula1('DATE(1900,01,01)');
            $validation->setFormula2('TODAY()');

            $sheet->setDataValidation($range, clone $validation);
        }
    }

    /**
     * Get a mapping of heading => callable returning dropdown options.
     */
    protected function getDropdownMappings(): array
    {
        return [
            'gender' => fn () => Usr::getGenders(),
            'blood_group_id' => fn () => Usr::getBloodGroups()->pluck('name')->toArray(),
            'lga_id' => fn () => $this->location->getStateById($this->data['state']->id)->pluck('name')->toArray(),
            'religion' => fn () => Usr::getReligions(),
            'section_id' => fn () => $this->my_class->getClassSections($this->data['class']->id)->pluck('name')->toArray(),
            'disability' => fn () => array_values(Usr::getDisabilities()),
            'parent_status' => fn () => Usr::getStudentParentsStatus(),
            'dorm_id' => fn () => $this->dorm->getAllDorms()->pluck('name')->toArray(),
        ];
    }

    /**
     * Apply header background and font colors.
     */
    protected function applyHeaderColors(Worksheet $sheet, int $end_col_index): void
    {
        $text_color = 'ffffff';
        $bg_color = '171717';

        $colors = Usr::getSetting('login_and_related_pgs_txts_and_bg_colors');
        if ($colors !== null) {
            $exploded = explode(Usr::getDelimiter(), $colors);
            $text_color = substr(str_replace('#', '', Color::parse($exploded[0])['hex'] ?? ''), 0, 6) ?: $text_color;
            $bg_color = substr(str_replace('#', '', Color::parse($exploded[1])['hex'] ?? ''), 0, 6) ?: $bg_color;
        }

        $header_range = [1, 1, $end_col_index, self::NUMBER_OF_HEADING_ROWS];
        $sheet->getStyle($header_range)
            ->getFill()
            ->applyFromArray(['fillType' => 'solid', 'color' => ['rgb' => $bg_color]]);
        $sheet->getStyle($header_range)
            ->getFont()
            ->applyFromArray(['color' => ['rgb' => $text_color]]);
    }

    /**
     * Convert a heading name to its corresponding column letter.
     */
    private function mapHeadingToLetter(string $heading): string
    {
        $index = array_search($heading, $this->all_headings);

        return Usr::numberToColumnLetter($index !== false ? $index + 1 : 1);
    }
}
