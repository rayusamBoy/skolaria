<?php

namespace App\Imports;

use App\Helpers\Mk;
use App\Models\SubjectRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class MarksImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected array $data;
    protected string $marks_table;
    protected Collection $subjects;
    protected array $subject_student_map = [];
    protected array $existing_marks = [];

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->marks_table = 'marks';
        $this->subjects = Mk::getSubjects($this->data['my_class_id']);
    }

    /**
     * Process the imported rows.
     */
    public function collection(Collection $rows): void
    {
        $this->preloadSubjectStudentMap();
        $this->preloadExistingMarks();

        $updates = [];

        foreach ($rows as $row) {
            $student_mark = $this->data['st_mark']
                ->where('user.username', $row['admission_number'])
                ->first();

            if (!$student_mark) {
                continue; // Skip if student not found
            }

            $student_id = $student_mark->user->id;
            $section_id = $student_mark->section_id;

            foreach ($this->subjects as $subject) {
                $subject_key = $this->replaceSpaceWithUnderscore($subject->name);
                $mark_value = $row[$subject_key] ?? null;

                // Skip if no mark provided and not explicitly zero
                if ($mark_value === null || $mark_value === '') {
                    continue;
                }

                $should_update = $this->shouldUpdateMark($student_id, $subject->id);

                if ($should_update) {
                    $updates[] = $this->buildUpdateData($student_id, $section_id, $subject->id, $mark_value);
                } else {
                    $this->deleteMark($student_id, $section_id, $subject->id);
                }
            }
        }

        $this->performBatchUpdate($updates);
    }

    /**
     * Build update data for a single mark.
     */
    protected function buildUpdateData(int $student_id, int $section_id, int $subject_id, $mark_value): array
    {
        return [
            'where' => [
                'student_id' => $student_id,
                'my_class_id' => $this->data['my_class_id'],
                'section_id' => $section_id,
                'exam_id' => $this->data['exam_id'],
                'year' => $this->data['year'],
                'subject_id' => $subject_id,
            ],
            'values' => ['exam_mark' => $mark_value],
        ];
    }

    /**
     * Perform batch update using a transaction for consistency.
     */
    protected function performBatchUpdate(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        DB::transaction(function () use ($updates) {
            foreach ($updates as $update) {
                DB::table($this->marks_table)
                    ->where($update['where'])
                    ->update($update['values']);
            }
        });
    }

    /**
     * Delete a mark.
     */
    public function deleteMark($sid, $sec_id, $sub_id)
    {
        $where = [
            'student_id' => $sid,
            'my_class_id' => $this->data['my_class_id'],
            'section_id' => $sec_id,
            'exam_id' => $this->data['exam_id'],
            'year' => $this->data['year'],
            'subject_id' => $sub_id,
        ];

        return DB::table($this->marks_table)->where($where)->delete();
    }

    /**
     * Replace spaces with underscores for Excel column matching.
     */
    public function replaceSpaceWithUnderscore($string): string
    {
        return Str::slug(strtolower($string), '_');
    }

    /**
     * Custom validation attribute names.
     */
    public function customValidationAttributes(): array
    {
        $attributes = [];
        foreach ($this->subjects as $subject) {
            $key = $this->replaceSpaceWithUnderscore($subject->name);
            $attributes[$key] = $subject->name;
        }

        return $attributes;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        $rules = ['*.admission_number' => ['required', 'string', 'exists:users,username']];

        foreach ($this->subjects as $subject) {
            $key = $this->replaceSpaceWithUnderscore($subject->name);
            $rules["*.$key"] = ['nullable', 'integer', 'min:0', 'max:100'];
        }

        return $rules;
    }

    /**
     * Heading row index.
     */
    public function headingRow(): int
    {
        return 4;
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Preload subject-student mapping to avoid repeated DB queries.
     */
    protected function preloadSubjectStudentMap(): void
    {
        $records = SubjectRecord::whereIn('subject_id', $this->subjects->pluck('id'))
            ->whereNotNull('students_ids')
            ->get(['subject_id', 'students_ids']);

        foreach ($records as $record) {
            $this->subject_student_map[$record->subject_id] = $record->students_ids ?? [];
        }
    }

    /**
     * Preload existing marks for the entire exam/class to detect missing marks for deletion.
     */
    protected function preloadExistingMarks(): void
    {
        $marks = DB::table($this->marks_table)
            ->where('my_class_id', $this->data['my_class_id'])
            ->where('exam_id', $this->data['exam_id'])
            ->where('year', $this->data['year'])
            ->get(['student_id', 'section_id', 'subject_id']);

        foreach ($marks as $mark) {
            $key = "{$mark->student_id}_{$mark->section_id}_{$mark->subject_id}";
            $this->existing_marks[$key] = true;
        }
    }

    /**
     * Determine if a mark should be updated (student is enrolled in subject).
     */
    protected function shouldUpdateMark(int $student_id, int $subject_id): bool
    {
        $allowed_students = $this->subject_student_map[$subject_id] ?? null;

        if ($allowed_students === null) {
            return true; // No restriction, all students take this subject
        }

        return \in_array($student_id, $allowed_students);
    }
}
