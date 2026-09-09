<?php

namespace App\Exports;

use App\Helpers\Mk;
use Dompdf\Css\Color;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarksExport implements FromView, ShouldAutoSize, WithEvents, WithProperties, WithStyles
{
    protected array $data;

    // S/N, ADMISSION NUMBER, FULL NAME, GENDER, SECTION
    protected const int INITIAL_COLS = 5;

    // Title and subject name rows
    protected const int HEADER_ROWS = 4;

    protected const string SHEET_PASSWORD = 'AllahKnowsBest';

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function properties(): array
    {
        $system_name = Mk::getSystemName();

        return [
            'creator' => $system_name,
            'lastModifiedBy' => $system_name,
            'title' => 'Marks Export',
            'description' => 'Excel marks export template for students marks',
            'subject' => 'Marks',
            'keywords' => 'marks,export,spreadsheet',
            'category' => 'Marks',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $subjects_count = Mk::getSubjects($this->data['my_class_id'])->count();
        $student_count = $this->data['marks']->unique('user.id')->whereNotNull('user')->count();

        $start_data_col = self::INITIAL_COLS + 1;
        $end_col_index = self::INITIAL_COLS + $subjects_count;
        $start_data_row = self::HEADER_ROWS + 1;
        $end_data_row = self::HEADER_ROWS + $student_count;

        $this->applyDataValidation($sheet, $start_data_col, $end_col_index, $start_data_row, $end_data_row);
        $this->applyProtection($sheet, $start_data_col, $end_col_index, $start_data_row, $end_data_row);
        $this->applyHeaderStyles($sheet, $end_col_index);
    }

    /**
     * Apply decimal validation (0–100) to all mark entry cells.
     */
    protected function applyDataValidation(
        Worksheet $sheet,
        int $start_col,
        int $end_col,
        int $start_row,
        int $end_row
    ): void {
        // Create validation template
        $validation = $sheet->getCell([$start_col, $start_row])->getDataValidation();
        $validation->setType(DataValidation::TYPE_DECIMAL);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('The input is not allowed!');
        $validation->setPromptTitle('Allowed input');
        $validation->setPrompt('Numbers only between 0 and 100.');
        $validation->setFormula1(0);
        $validation->setFormula2(100);

        // Build range string (e.g., "E5:G20")
        $range = Coordinate::stringFromColumnIndex($start_col).$start_row.':'.
            Coordinate::stringFromColumnIndex($end_col).$end_row;

        $sheet->setDataValidation($range, clone $validation);
    }

    /**
     * Protect the sheet but leave mark entry cells unlocked.
     */
    protected function applyProtection(
        Worksheet $sheet,
        int $start_col,
        int $end_col,
        int $start_row,
        int $end_row
    ): void {
        $sheet->getProtection()->setSheet(true);
        $sheet->getProtection()->setPassword(self::SHEET_PASSWORD);

        // Unlock the mark entry range
        $sheet->getStyle([$start_col, $start_row, $end_col, $end_row])
            ->getProtection()
            ->setLocked(Protection::PROTECTION_UNPROTECTED);
    }

    /**
     * Apply background and font colors to header rows.
     */
    protected function applyHeaderStyles(Worksheet $sheet, int $end_col): void
    {
        [$texts_color, $bg_color] = $this->getHeaderColors();

        $header_range = [1, 1, $end_col, self::HEADER_ROWS];
        $sheet->getStyle($header_range)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($bg_color);

        $sheet->getStyle($header_range)
            ->getFont()
            ->getColor()
            ->setRGB($texts_color);
    }

    /**
     * Retrieve text and background colors from system settings.
     */
    protected function getHeaderColors(): array
    {
        $colors = Mk::getSetting('login_and_related_pgs_txts_and_bg_colors');

        if ($colors === null) {
            return ['ffffff', '171717']; // default white text, dark gray background
        }

        $exploded = explode(Mk::getDelimiter(), $colors);
        $text_color = substr(str_replace('#', '', Color::parse($exploded[0])['hex'] ?? ''), 0, 6) ?? 'ffffff';
        $bg_color = substr(str_replace('#', '', Color::parse($exploded[1])['hex'] ?? ''), 0, 6) ?? '171717';

        return [$text_color, $bg_color];
    }

    /**
     * Register events (after sheet creation).
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Hide column C
                $sheet->getColumnDimension('C')->setVisible(false);

                // Freeze rows 1-4 and columns A-B
                $sheet->freezePane('C5');
            },
        ];
    }

    public function view(): View
    {
        $this->data['initial_cols'] = self::INITIAL_COLS;

        return view('pages.marks.batch.table', $this->data);
    }
}
