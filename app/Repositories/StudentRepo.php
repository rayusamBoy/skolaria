<?php

namespace App\Repositories;

use App\Helpers\Qs;
use App\Models\Dorm;
use App\Models\Promotion;
use App\Models\StudentRecord;

class StudentRepo
{
    /*********** Student record ***************/

    public function getRecord(array $where = [], array $with = ['my_class', 'section', 'user'])
    {
        return StudentRecord::where($where)->with($with)->get();
    }

    public static function getRecordValue(array $where, string $col_name)
    {
        return StudentRecord::where($where)->value($col_name);
    }

    public function findStudentsByClass($class_id, array $with = ['user'])
    {
        // Get all students by class except deleted ones. If user deleted, the user will return null, hence exclude null from the collection
        return $this->activeStudents()->where(['my_class_id' => $class_id])->with($with)->get()->whereNotNull('user')->sortBy('user.name');
    }

    public function activeStudents()
    {
        return StudentRecord::where('grad', false);
    }

    public function promotedWhereGrad()
    {
        return Promotion::where('grad', true);
    }

    public function allActiveStudents()
    {
        return $this->activeStudents()->with(['my_class', 'section', 'user'])->get()->sortBy('user.name');
    }

    public function allGradOnlyStudents()
    {
        return $this->promotedWhereGrad()->get();
    }

    public function allGradStudents()
    {
        return $this->promotedWhereGrad()->with(['student', 'user', 'fc', 'tc', 'fs', 'ts'])->get()->sortBy('user.name');
    }

    public function findStudentsBySection($sec_id)
    {
        return $this->activeStudents()->where('section_id', $sec_id)->with(['user', 'my_class'])->get();
    }

    public function createRecord(array $data)
    {
        return StudentRecord::create($data);
    }

    public function updateRecord($value, array $data, string $column = 'id')
    {
        return StudentRecord::where($column, $value)->update($data);
    }

    public function updateOrCreateRecord(array $where, array $data)
    {
        return StudentRecord::updateOrCreate($where, $data);
    }

    public function whereRecord(array $where, $with = ['user', 'my_parent'])
    {
        return $this->activeStudents()->where($where)->with($with);
    }

    public function whereGraduatesRecord(array $where, array $with = ['user', 'my_parent'])
    {
        return StudentRecord::where('grad', true)->where($where)->with($with);
    }

    public function getRecordByUserIDs($ids)
    {
        return $this->activeStudents()->whereIn('user_id', $ids)->with('user');
    }

    public function getRecordByUserIDs2($ids)
    {
        return StudentRecord::whereIn('user_id', $ids)->with('user');
    }

    public function getAll()
    {
        return $this->activeStudents()->with('user');
    }

    public function getGradRecord(array $data = [])
    {
        return $this->promotedWhereGrad()->where($data)->with('user');
    }

    public function getAllDorms()
    {
        return Dorm::orderBy('name', 'asc')->get();
    }

    public function exists($student_id)
    {
        $exists_as_active = $this->whereRecord(['user_id' => $student_id])->exists();
        return $exists_as_active == true ? $exists_as_active : $this->whereGraduatesRecord(['user_id' => $student_id])->exists();
    }

    /************* Promotions *************/

    public function createPromotion(array $data)
    {
        return Promotion::create($data);
    }

    public function updatePromotion(array $where, array $data)
    {
        return Promotion::where($where)->update($data);
    }

    public function findPromotion($id)
    {
        return Promotion::find($id);
    }

    public function deletePromotion(int $id)
    {
        return Promotion::destroy($id);
    }

    public function getAllPromotions()
    {
        return Promotion::with(['student', 'fc', 'tc', 'fs', 'ts'])->where(['from_session' => Qs::getCurrentSession(), 'to_session' => Qs::getNextSession()])->get();
    }

    public function getPromotions(array $where)
    {
        return Promotion::where($where)->get();
    }
}
