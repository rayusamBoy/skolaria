<?php

namespace App\Repositories;

use App\Models\Grade;
use App\Models\Mark;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MarkRepo
{
    /*********** Marks ***************/

    /**
     * Get marks for a set of student IDs, with eager loaded relations.
     */
    public function getMarksForStudents(array $ids, array $with = ['subject', 'user']): Builder
    {
        return Mark::with($with)->whereIn('student_id', $ids);
    }

    /**
     * Get marks for a specific student, with subject and user relations.
     */
    public function getMarksByStudentId(int $student_id): Builder
    {
        return Mark::with(['subject', 'user'])->where('student_id', $student_id);
    }

    /**
     * Get the total mark for a student in a specific subject and term.
     */
    public function getSubjectTermTotal(int $st_id, int $sub_id, int $term, int $class_id, string $year): ?float
    {
        $data = [
            'student_id' => $st_id,
            'subject_id' => $sub_id,
            'my_class_id' => $class_id,
            'year' => $year,
        ];
        $tex = "tex$term";
        $record = Mark::where($data)
            ->whereNotNull($tex)
            ->where($tex, '>', 0)
            ->select($tex)
            ->first();

        return $record ? (float) $record->{$tex} : null;
    }

    /**
     * Compute total term marks for a student in an exam and class.
     *
     * @param  mixed  $exam  Exam model with 'id' and 'term'
     */
    public function getTermTotalForExam($exam, int $st_id, int $class_id, string $year): float
    {
        $data = [
            'student_id' => $st_id,
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'year' => $year,
        ];
        $tex = "tex{$exam->term}";

        return (float) Mark::where($data)->sum($tex);
    }

    /**
     * Compute total term marks for a student in an exam, class, and section.
     *
     * @param  mixed  $exam  Exam model with 'id' and 'term'
     */
    public function getTermTotalForExamAndSection($exam, int $st_id, int $class_id, int $section_id, string $year): float
    {
        $data = [
            'student_id' => $st_id,
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'section_id' => $section_id,
            'year' => $year,
        ];
        $tex = "tex{$exam->term}";

        return (float) Mark::where($data)->sum($tex);
    }

    /**
     * Compute the limited exam average for a student using only the top‑scoring core subjects.
     *
     * @param  mixed  $exam  Exam model with 'id' and 'term'
     * @param  int  $limit  number of top subjects to consider
     */
    public function getLimitedTermAverage($exam, int $st_id, int $class_id, int $sec_id, string $year, int $limit): float
    {
        $where = [
            'student_id' => $st_id,
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'section_id' => $sec_id,
            'year' => $year,
        ];
        $tex = "tex{$exam->term}";
        $avg = Mark::with('subject')
            ->where($where)
            ->where($tex, '>=', 0)
            ->get()
            ->where('subject.is_core')
            ->sortByDesc($tex)
            ->when($limit > 0, fn ($q) => $q->take($limit))
            ->avg($tex);

        return round((float) $avg, 1);
    }

    /**
     * Compute cumulative total across three terms for a student, subject, and class.
     *
     * @param  float  $tex3  Third term mark
     */
    public function getSubjectCumulativeTotal(float $tex3, int $st_id, int $sub_id, int $class_id, string $year): float
    {
        $tex1 = $this->getSubjectTermTotal($st_id, $sub_id, 1, $class_id, $year) ?? 0;
        $tex2 = $this->getSubjectTermTotal($st_id, $sub_id, 2, $class_id, $year) ?? 0;

        return $tex1 + $tex2 + (float) $tex3;
    }

    /**
     * Compute cumulative average across three terms for a student, subject, and class.
     */
    public function getSubjectCumulativeAverage(float $tex3, int $st_id, int $sub_id, int $class_id, string $year): float
    {
        $tex1 = $this->getSubjectTermTotal($st_id, $sub_id, 1, $class_id, $year);
        $tex2 = $this->getSubjectTermTotal($st_id, $sub_id, 2, $class_id, $year);
        $count = ($tex1 !== null ? 1 : 0)
            + ($tex2 !== null ? 1 : 0)
            + ($tex3 !== null ? 1 : 0);
        $total = (float) ($tex1 ?? 0) + ($tex2 ?? 0) + (float) ($tex3 ?? 0);

        return $total > 0 ? round($total / $count, 1) : 0;
    }

    /**
     * Get the raw term mark for a student, subject, exam, and class.
     *
     * @param  mixed  $exam
     */
    public function getSubjectTermMark($exam, int $class_id, int $sub_id, int $st_id, string $year): ?float
    {
        $d = [
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'subject_id' => $sub_id,
            'student_id' => $st_id,
            'year' => $year,
        ];
        $tex = "tex{$exam->term}";
        $record = Mark::where($d)->select($tex)->first();

        return $record->$tex ?? null;
    }

    /**
     * Compute the student's subject‑wise position (rank) in a class.
     *
     * @param  mixed  $exam
     */
    public function getSubjectPosition(int $st_id, $exam, int $class_id, int $sub_id, string $year): ?int
    {
        $d = [
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'subject_id' => $sub_id,
            'year' => $year,
        ];
        $tex = "tex{$exam->term}";
        $sub_mk = $this->getSubjectTermMark($exam, $class_id, $sub_id, $st_id, $year);
        $all_marks = Mark::where($d)
            ->whereNotNull($tex)
            ->orderByDesc($tex)
            ->pluck($tex);
        $position = $all_marks->search($sub_mk);

        return $position !== false ? $position + 1 : null;
    }

    /**
     * Count the number of subjects a student took in a given exam.
     *
     * @param  mixed  $exam
     */
    public function countExamSubjects($exam, int $st_id, int $class_id, string $year): int
    {
        $d = [
            'my_class_id' => $class_id,
            'student_id' => $st_id,
            'year' => $year,
        ];
        // For third term, ignore exam_id
        if ($exam->term != 3) {
            $d['exam_id'] = $exam->id;
        }
        $tex = "tex{$exam->term}";

        return Mark::where($d)->whereNotNull($tex)->count();
    }

    /**
     * Compute the class average for a given exam.
     *
     * @param  mixed  $exam
     */
    public function getClassAverage($exam, int $class_id, string $year): float
    {
        $d = [
            'exam_id' => $exam->id,
            'my_class_id' => $class_id,
            'year' => $year,
        ];
        $tex = "tex{$exam->term}";
        $avg = Mark::where($d)->avg($tex);

        return round((float) $avg, 1);
    }

    /**
     * Sum the smallest values in an array.
     *
     * @param  array<int|float>  $weights  Numeric values to evaluate.
     * @param  int  $length  Number of smallest values to sum.
     * @return int|float|null The sum, or null if input is invalid/insufficient.
     */
    public function sumSmallestValues(array $weights, int $length): int|float|null
    {
        if ($length < 0) {
            return null;
        }
        if (\count($weights) < $length) {
            return null;
        }
        $sorted = $weights;
        sort($sorted);

        return $length === 0 ? array_sum($sorted) : array_sum(\array_slice($sorted, 0, $length));
    }

    /**
     * Get a single column value from the first mark record matching conditions.
     *
     * @param  array  $data  Conditions
     * @param  string  $value  Column name
     * @return mixed|null
     */
    public function getMarkValue(array $data, string $value): mixed
    {
        return Mark::where($data)->value($value);
    }

    /**
     * Get distinct subject IDs from marks matching conditions.
     */
    public function getDistinctSubjectIds(array $where): Collection
    {
        return Mark::distinct()->select('subject_id')->where($where)->pluck('subject_id');
    }

    /**
     * Get distinct student IDs from marks matching conditions.
     */
    public function getDistinctStudentIds(array $where): Collection
    {
        return Mark::distinct()->select('student_id')->where($where)->pluck('student_id');
    }

    /**
     * Find a mark record by attributes or create a new one.
     *
     * @param  int  $sid  student_id
     * @param  int  $class_id  my_class_id
     * @param  int  $sec_id  section_id
     * @param  int  $sub_id  subject_id
     */
    public function firstOrCreateMark(int $sid, int $class_id, int $sec_id, int $exam_id, string $year, int $sub_id): Mark
    {
        return Mark::firstOrCreate([
            'student_id' => $sid,
            'my_class_id' => $class_id,
            'section_id' => $sec_id,
            'exam_id' => $exam_id,
            'year' => $year,
            'subject_id' => $sub_id,
        ]);
    }

    /*********** Grade ***************/

    /**
     * Get the grade for a given total mark and class type.
     *
     * @param  float  $total  The total mark
     */
    public function getGradeByTotal(float $total, int $class_type_id): ?Grade
    {
        if ($total < 0) {
            return null;
        }
        $rounded = (int) round($total);
        $grade = Grade::where('class_type_id', $class_type_id)
            ->where('mark_from', '<=', $rounded)
            ->where('mark_to', '>=', $rounded)
            ->first();

        return $grade ?? $this->getGlobalGradeByTotal($rounded);
    }

    /**
     * Get a grade from the global (class‑type‑independent) grade set.
     */
    public function getGlobalGradeByTotal(float $total): ?Grade
    {
        $rounded = (int) round($total);

        return Grade::whereNull('class_type_id')
            ->where('mark_from', '<=', $rounded)
            ->where('mark_to', '>=', $rounded)
            ->first();
    }
}
