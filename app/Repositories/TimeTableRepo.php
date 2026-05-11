<?php

namespace App\Repositories;

use App\Models\TimeSlot;
use App\Models\TimeTable;
use App\Models\TimeTableRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TimeTableRepo
{
    /*********** Timetables ***************/

    /**
     * Get timetable entries with subject and time slot, ordered by time.
     *
     * @param array $where
     * @return Collection
     */
    public function getTimeTableEntries(array $where): Collection
    {
        return TimeTable::with(['subject', 'time_slot'])
            ->orderBy('timestamp_from')
            ->where($where)
            ->get();
    }

    /**
     * Create a new timetable entry.
     *
     * @param array $data
     * @return TimeTable
     */
    public function create(array $data): TimeTable
    {
        return TimeTable::create($data);
    }

    /**
     * Update a timetable entry by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function update(int $id, array $data): int
    {
        return TimeTable::where('id', $id)->update($data);
    }

    /**
     * Delete a timetable entry by ID.
     *
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return TimeTable::destroy($id);
    }

    /************* TIME SLOTS ***********/

    /**
     * Get a query builder for time slots matching conditions, ordered by start time.
     *
     * @param array $where
     * @return Builder
     */
    public function getTimeSlotsQuery(array $where): Builder
    {
        return TimeSlot::orderBy('timestamp_from')->where($where);
    }

    /**
     * Get all time slots belonging to a specific timetable record.
     *
     * @param int $ttr_id
     * @return Collection
     */
    public function getTimeSlotsByRecord(int $ttr_id): Collection
    {
        return $this->getTimeSlotsQuery(['ttr_id' => $ttr_id])->get();
    }

    /**
     * Get distinct timetable record IDs from time slots, optionally excluding one.
     *
     * @param int|null $remove_ttr
     * @return \Illuminate\Support\Collection
     */
    protected function getDistinctRecordIds(?int $remove_ttr = null): \Illuminate\Support\Collection
    {
        $query = TimeSlot::distinct()->select('ttr_id');

        if ($remove_ttr !== null) {
            $query->where('ttr_id', '<>', $remove_ttr);
        }

        return $query->pluck('ttr_id');
    }

    /**
     * Get existing timetable records (excluding a given one if provided) that have time slots defined.
     *
     * @param int|null $remove_ttr
     * @return Collection
     */
    public function getRecordsWithTimeSlots(?int $remove_ttr = null): Collection
    {
        $ids = $this->getDistinctRecordIds($remove_ttr);
        return $this->getRecordsByIds($ids);
    }

    /**
     * Create a new time slot.
     *
     * @param array $data
     * @return TimeSlot
     */
    public function createTimeSlot(array $data): TimeSlot
    {
        return TimeSlot::create($data);
    }

    /**
     * Delete a single time slot by ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteTimeSlot(int $id): int
    {
        return TimeSlot::destroy($id);
    }

    /**
     * Delete time slots matching conditions.
     *
     * @param array $where
     * @return int
     */
    public function deleteTimeSlots(array $where): int
    {
        return TimeSlot::where($where)->delete();
    }

    /**
     * Update a time slot by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateTimeSlot(int $id, array $data): int
    {
        return TimeSlot::where('id', $id)->update($data);
    }

    /**
     * Find a time slot by ID or fail with 404.
     *
     * @param int $id
     * @return TimeSlot
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findTimeSlot(int $id): TimeSlot
    {
        return TimeSlot::findOrFail($id);
    }

    /************* Timetable records ***********/

    /**
     * Get all timetable records with class, exam, and section.
     *
     * @return Collection
     */
    public function getAllTimetableRecords(): Collection
    {
        return TimeTableRecord::orderBy('created_at')
            ->with(['my_class', 'exam', 'section'])
            ->get();
    }

    /**
     * Get timetable records by a list of IDs.
     *
     * @param array|\Illuminate\Support\Collection $ids
     * @return Collection
     */
    public function getRecordsByIds($ids): Collection
    {
        return TimeTableRecord::orderBy('name')
            ->whereIn('id', $ids instanceof \Illuminate\Support\Collection ? $ids->toArray() : $ids)
            ->get();
    }

    /**
     * Create a new timetable record.
     *
     * @param array $data
     * @return TimeTableRecord
     */
    public function createTimetableRecord(array $data): TimeTableRecord
    {
        return TimeTableRecord::create($data);
    }

    /**
     * Update a timetable record by ID.
     *
     * @param int   $id
     * @param array $data
     * @return int
     */
    public function updateTimetableRecord(int $id, array $data): int
    {
        return TimeTableRecord::where('id', $id)->update($data);
    }

    /**
     * Delete a timetable record by ID.
     *
     * @param int $id
     * @return int
     */
    public function deleteTimetableRecord(int $id): int
    {
        return TimeTableRecord::destroy($id);
    }

    /**
     * Find a timetable record by ID (may return null).
     *
     * @param int $id
     * @return TimeTableRecord|null
     */
    public function findTimetableRecord(int $id): ?TimeTableRecord
    {
        return TimeTableRecord::find($id);
    }
}
