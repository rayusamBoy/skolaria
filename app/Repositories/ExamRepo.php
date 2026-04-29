<?php

namespace App\Repositories;

use App\Models\ClassType;
use App\Models\Division;
use App\Models\Exam;
use App\Models\ExamAnnounce;
use App\Models\ExamCategory;
use App\Models\ExamHasAssessment;
use App\Models\ExamNumberFormat;
use App\Models\ExamRecord;
use App\Models\Grade;
use App\Models\Mark;
use App\Models\Section;
use App\Models\Skill;
use Illuminate\Support\Facades\DB;

class ExamRepo
{
    protected $marks_table, $exam_records_table;
    public function __construct()
    {
        $this->marks_table = 'marks';
        $this->exam_records_table = 'exam_records';
    }

    /*********** Exam category ***************/

    public function getCategories()
    {
        return ExamCategory::get();
    }

    /*********** Exam Announce ***************/

    public function announce($data)
    {
        return ExamAnnounce::firstOrCreate($data);
    }

    /*********** Exam number format ***************/

    public function createNumberFormat($data)
    {
        return ExamNumberFormat::firstOrCreate($data);
    }

    public function updateNumberFormat($id, $data)
    {
        return ExamNumberFormat::where('id', $id)->update($data);
    }

    public function updateOrCreateNumberFormat($attributes, $values)
    {
        return ExamNumberFormat::updateOrCreate($attributes, $values);
    }

    public function deleteNumberFormat($id)
    {
        return ExamNumberFormat::find($id)->delete();
    }

    /*********** Exam ***************/

    public function onlyPublished($with = ['category'])
    {
        return Exam::where('published', true)->orderByDesc('created_at')->orderBy('year', 'desc')->with($with)->get();
    }

    public function getPublished(mixed $where)
    {
        return Exam::where($where)->where('published', true)->get();
    }

    public function all($with = ['category', 'class_type'])
    {
        return auth()->user()->can(config('permissions.names.tenant.exams.publish.permission')) ? Exam::orderByDesc('created_at')->orderBy('year', 'desc')->with($with)->get() : $this->onlyPublished($with);
    }

    public function getById(int $id, $only_published = true, array $with = ['category', 'class_type'])
    {
        return $only_published ? Exam::where('id', $id)->where('published', true)->with($with)->first() : Exam::where('id', $id)->with($with)->first();
    }

    public function getExam(array $where, bool $without_locked_ones = true, $with = [])
    {
        return ($without_locked_ones === true) ? Exam::where($where)->latest()->where('locked', false)->with($with)->get() : Exam::where($where)->latest()->with($with)->get();
    }

    public function exists(int $exam_id)
    {
        return Exam::where('id', $exam_id)->exists();
    }

    public function getTrashed(array $with = ['category'])
    {
        return Exam::onlyTrashed()->with($with)->get();
    }

    public function examsExists()
    {
        return Exam::get()->isNotEmpty();
    }

    public function find(int $id)
    {
        return Exam::find($id);
    }

    public function firstOrCreate(array $data)
    {
        return Exam::firstOrCreate($data);
    }

    public function updateOrCreate(array $attributes, array $values)
    {
        return Exam::updateOrCreate($attributes, $values);
    }

    public function create(array $data)
    {
        return Exam::create($data);
    }

    public function getExamsCount()
    {
        return Exam::all()->count();
    }

    public function getPublishedOrderByLatest()
    {
        return Exam::latest()->where('published', true)->get();
    }

    public function getPublishedExamsCount()
    {
        return Exam::where('published', true)->get()->count();
    }

    public function update(int $id, array $data)
    {
        return Exam::find($id)->update($data);
    }

    public function delete($id)
    {
        return Exam::destroy($id);
    }

    public function restore(int $id)
    {
        return Exam::where('id', $id)->restore();
    }

    public function forceDelete(int $id)
    {
        return Exam::where('id', $id)->forceDelete();
    }

    public function isLocked(int $id)
    {
        return Exam::where('id', $id)->where('locked', true)->exists();
    }

    public function getYears()
    {
        return Exam::select('year')->distinct()->get();
    }

    /*********** Exam Record ***************/

    public function createRecord($data)
    {
        return ExamRecord::firstOrCreate($data);
    }

    public function deleteRecord($where)
    {
        return ExamRecord::where($where)->delete();
    }

    public function countAllExamRecords()
    {
        return ExamRecord::all()->count();
    }

    public function updateRecord($where, $data)
    {
        return ExamRecord::where($where)->update($data);
    }

    public function updateRecordDBFacades($where, $data)
    {
        return DB::table($this->exam_records_table)->where($where)->update($data);
    }

    public function massUpdateExamRec(array $values, array $unique_by = ['id'])
    {
        return ExamRecord::massUpdate(
            values: $values,
            uniqueBy: $unique_by
        );
    }

    public function getRecord(array $where, array $with = [])
    {
        return ExamRecord::where($where)->with($with)->get();
    }

    public function getRecordsByStudentId($id)
    {
        return ExamRecord::with('exam')->with('student')->where('student_id', $id);
    }

    public function getRecordsById($id)
    {
        return ExamRecord::with('my_class')->where('exam_id', $id);
    }

    public function getRecordsByIds($ids)
    {
        return ExamRecord::with('exam')->with('student')->whereIn('student_id', $ids);
    }

    public function firstOrCreateExmRec($sid, $class_id, $sec_id, $exam_id, $year)
    {
        return ExamRecord::firstOrCreate([
            'student_id' => $sid,
            'my_class_id' => $class_id,
            'section_id' => $sec_id,
            'exam_id' => $exam_id,
            'year' => $year,
        ]);
    }

    public function findRecord($id)
    {
        return ExamRecord::find($id);
    }

    public function getStudentValue($where, $value)
    {
        return ExamRecord::where($where)->select($value)->get()->first()->$value;
    }

    public function getStudentPos($st_id, $exam_id, $class_id, $year, $value, $section_id = NULL)
    {
        $where = ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'student_id' => $st_id, 'year' => $year];
        $std_value = $this->getStudentValue($where, $value);

        $data = $section_id != NULL
            ? ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'section_id' => $section_id, 'year' => $year]
            : $data = ['exam_id' => $exam_id, 'my_class_id' => $class_id, 'year' => $year];

        $std_values = ExamRecord::where($data)->whereNotNull($value)->orderBy($value, 'DESC')->select($value)->get()->pluck($value);
        if ($std_values->count() <= 0)
            return NULL;

        $Tkey = $std_values->search($std_value);

        return $Tkey === false ? NULL : $Tkey + 1;
    }

    /*********** Grades ***************/

    public function allGrades()
    {
        return Grade::orderBy('class_type_id')->orderBy('name')->get();
    }

    public function getGrade($data, array $with = [])
    {
        return Grade::where($data)->with($with)->get();
    }

    public function findGrade($id)
    {
        return Grade::find($id);
    }

    public function createGrade(array $data)
    {
        return Grade::create($data);
    }

    public function updateGrade($id, array $data)
    {
        return Grade::find($id)->update($data);
    }

    public function deleteGrade($id)
    {
        return Grade::destroy($id);
    }

    /*********** Marks ***************/

    public function createMark(array $data)
    {
        return Mark::firstOrCreate($data);
    }

    public function destroyMark($id)
    {
        return Mark::destroy($id);
    }

    public function deleteMark(array $where)
    {
        return Mark::where($where)->delete();
    }

    public function updateMark(int $id, array $data)
    {
        return Mark::find($id)->update($data);
    }

    public function massUpdateMark(array $values, array $unique_by = ['id'])
    {
        return Mark::massUpdate(
            values: $values,
            uniqueBy: $unique_by
        );
    }

    public function updateMarkDBFacades(int $id, array $data)
    {
        return DB::table($this->marks_table)->where('id', $id)->update($data);
    }

    public function getStudentExamYears($student_id)
    {
        return Mark::where('student_id', $student_id)->select('year')->distinct()->get();
    }

    public function getMark(array $where, array $with = ['grade', 'user', 'subject', 'section'])
    {
        return Mark::where($where)->with($with)->get();
    }

    public function getMarkByIds($ids)
    {
        return Mark::whereIn('student_id', $ids);
    }

    public function getRecordValue(array $where, $value)
    {
        return Mark::where($where)->value($value);
    }

    public function getGrades()
    {
        return Grade::get();
    }

    /*********** Skills ***************/

    public function getSkill(array $where)
    {
        return Skill::where($where)->orderBy('name')->get();
    }

    public function getSkillByClassType($class_type_id = NULL, $skill_type = NULL)
    {
        return $skill_type
            ? $this->getSkill(['class_type_id' => $class_type_id, 'skill_type' => $skill_type])
            : $this->getSkill(['class_type_id' => $class_type_id]);
    }

    /*********** Sections ***************/

    public function getSections(array $where)
    {
        return Section::where($where)->get();
    }

    /*********** Divisions ***************/

    public function allDivisions()
    {
        return Division::orderBy('class_type_id')->orderBy('name')->get();
    }

    public function getDivision(array $where, array $with)
    {
        return Division::where($where)->with($with)->get();
    }

    public function findDivision($id)
    {
        return Division::find($id);
    }

    public function createDivision(array $data)
    {
        return Division::create($data);
    }

    public function updateDivision($id, array $data)
    {
        return Division::find($id)->update($data);
    }

    public function deleteDivision($id)
    {
        return Division::destroy($id);
    }

    /*********** Class types ***************/

    public function getClassTypes()
    {
        return ClassType::get();
    }

    /*********** Exam has Assessments ***************/
    public function firstOrCreateExamHasAssessment(array $data)
    {
        return ExamHasAssessment::firstOrCreate($data);
    }
}
