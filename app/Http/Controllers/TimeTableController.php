<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Http\Requests\TimeTable\TSRequest;
use App\Http\Requests\TimeTable\TTRecordRequest;
use App\Http\Requests\TimeTable\TTRequest;
use App\Repositories\ExamRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\TimeTableRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class TimeTableController extends Controller
{
    protected TimeTableRepo $tt;
    protected MyClassRepo $my_class;
    protected ExamRepo $exam;
    protected string $year;

    public function __construct(TimeTableRepo $tt, MyClassRepo $my_class, ExamRepo $exam)
    {
        $this->permissionMap = config('permissions.names.tenant.time_tables');
        parent::__construct();
        $this->tt = $tt;
        $this->my_class = $my_class;
        $this->exam = $exam;
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Display a listing of timetable records.
     */
    public function index(): View
    {
        $data['exams'] = $this->exam->getExamsByConditions(['year' => $this->year]);
        $data['tt_records'] = $this->tt->getAllTimetableRecords();

        return view('pages.timetables.index', $data);
    }

    /**
     * Show the timetable detail for a record.
     */
    public function manage_record($ttr_id): View
    {
        $ttr = $this->tt->findTimetableRecord($ttr_id);

        $data['ttr_id'] = $ttr_id;
        $data['ttr'] = $ttr;
        $data['time_slots'] = $this->tt->getTimeSlotsByRecord($ttr_id);
        $data['ts_existing'] = $this->tt->getRecordsWithTimeSlots($ttr_id);
        $data['subjects'] = $this->my_class->whereSubject(['my_class_id' => $ttr->my_class_id])->get();
        $data['my_class'] = $this->my_class->findClass($ttr->my_class_id);

        if ($ttr->exam_id) {
            $data['exam_id'] = $ttr->exam_id;
            $data['exam'] = $this->exam->find($ttr->exam_id);
        }

        $data['tts'] = $this->tt->getTimeTableEntries(['ttr_id' => $ttr_id]);

        return view('pages.timetables.manage', $data);
    }

    /**
     * Store a newly created timetable entry.
     */
    public function store(TTRequest $req): JsonResponse
    {
        $data = $req->all();
        $tms = $this->tt->findTimeSlot($req->ts_id);
        $d_date = $req->exam_date ?? $req->day;
        $data['timestamp_from'] = strtotime("$d_date {$tms->time_from}");
        $data['timestamp_to'] = strtotime("$d_date {$tms->time_to}");

        $this->tt->create($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Update the specified timetable entry.
     */
    public function update(TTRequest $req, $tt_id): RedirectResponse
    {
        $data = $req->all();
        $tms = $this->tt->findTimeSlot($req->ts_id);
        $d_date = $req->exam_date ?? $req->day;
        $data['timestamp_from'] = strtotime("$d_date {$tms->time_from}");
        $data['timestamp_to'] = strtotime("$d_date {$tms->time_to}");

        $this->tt->update($tt_id, $data);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Remove the specified timetable entry.
     */
    public function delete($tt_id): RedirectResponse
    {
        $this->tt->delete($tt_id);

        return back()->with('flash_success', __('msg.delete_ok'));
    }

    /*********** TIME SLOTS *************/

    /**
     * Store a new time slot.
     */
    public function store_time_slot(TSRequest $req): JsonResponse
    {
        $data = $req->all();
        $tf = "{$req->hour_from}:{$req->min_from} {$req->meridian_from}";
        $tt = "{$req->hour_to}:{$req->min_to} {$req->meridian_to}";
        $data['time_from'] = $tf;
        $data['time_to'] = $tt;
        $data['timestamp_from'] = strtotime($tf);
        $data['timestamp_to'] = strtotime($tt);
        $data['full'] = "$tf - $tt";

        if ($tf == $tt) {
            return response()->json(['msg' => __('msg.invalid_time_slot'), 'ok' => false]);
        }

        $this->tt->createTimeSlot($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Use time slots from another timetable record.
     */
    public function use_time_slot(Request $req, $ttr_id): RedirectResponse
    {
        Validator::make($req->toArray(), [
            'ttr_id' => 'required',
        ], [], ['ttr_id' => 'TimeTable Record'])->validate();

        $this->tt->deleteTimeSlots(['ttr_id' => $ttr_id]);
        $time_slots = $this->tt->getTimeSlotsByRecord($req->ttr_id)->toArray();

        foreach ($time_slots as $ts) {
            $ts['ttr_id'] = $ttr_id;
            $this->tt->createTimeSlot($ts);
        }

        return redirect()->route('ttr.manage', $ttr_id)->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Show the form for editing a time slot.
     */
    public function edit_time_slot($ts_id): View
    {
        $data['tms'] = $this->tt->findTimeSlot($ts_id);

        return view('pages.timetables.time_slots.edit', $data);
    }

    /**
     * Update the specified time slot.
     */
    public function update_time_slot(TSRequest $req, $ts_id): RedirectResponse
    {
        $data = $req->all();
        $tf = "{$req->hour_from}:{$req->min_from} {$req->meridian_from}";
        $tt = "{$req->hour_to}:{$req->min_to} {$req->meridian_to}";
        $data['time_from'] = $tf;
        $data['time_to'] = $tt;
        $data['timestamp_from'] = $timestamp_from = strtotime($tf);
        $data['timestamp_to'] = $timestamp_to = strtotime($tt);
        $data['full'] = "$tf - $tt";

        if ($timestamp_from > $timestamp_to) {
            return back()->with('flash_danger', __('msg.invalid_time_flow'));
        }
        if ($tf == $tt) {
            return back()->with('flash_danger', __('msg.invalid_time_slot'));
        }

        $this->tt->updateTimeSlot($ts_id, $data);

        return redirect()->route('ttr.manage', $req->ttr_id)->with('flash_success', __('msg.update_ok'));
    }

    /**
     * Remove the specified time slot.
     */
    public function delete_time_slot($ts_id): RedirectResponse
    {
        $this->tt->deleteTimeSlot($ts_id);

        return back()->with('flash_success', __('msg.delete_ok'));
    }

    /*********** RECORDS *************/

    /**
     * Show the form for editing a timetable record.
     */
    public function edit_record($ttr_id): View
    {
        $ttr = $this->tt->findTimetableRecord($ttr_id);
        $data['ttr'] = $ttr;
        $data['exams'] = $this->exam->getExamsByConditions(['year' => $ttr->year]);
        $data['sections'] = $this->exam->getSectionsByConditions(['my_class_id' => $ttr->my_class_id]);

        return view('pages.timetables.edit', $data);
    }

    /**
     * Display the timetable in a readable layout.
     */
    public function show_record($ttr_id): View
    {
        $ttr = $this->tt->findTimetableRecord($ttr_id);
        $tms = $this->tt->getTimeSlotsByRecord($ttr_id);
        $tts = $this->tt->getTimeTableEntries(['ttr_id' => $ttr_id]);

        $days = $ttr->exam_id
            ? $tts->unique('exam_date')->pluck('exam_date')
            : $tts->unique('day')->pluck('day');
        $date_field = $ttr->exam_id ? 'exam_date' : 'day';

        $d_time = [];
        foreach ($days as $day) {
            foreach ($tms as $tm) {
                $entry = $tts->where('ts_id', $tm->id)->where($date_field, $day)->first();
                $d_time[] = [
                    'day' => $day,
                    'time' => $tm->full,
                    'subject' => $entry->subject->name ?? null,
                ];
            }
        }

        $data['ttr_id'] = $ttr_id;
        $data['ttr'] = $ttr;
        $data['my_class'] = $this->my_class->findClass($ttr->my_class_id);
        $data['time_slots'] = $tms;
        $data['tts'] = $tts;
        $data['days'] = $days;
        $data['d_time'] = collect($d_time);

        if ($ttr->exam_id) {
            $data['exam_id'] = $ttr->exam_id;
            $data['exam'] = $this->exam->find($ttr->exam_id);
        }

        return view('pages.timetables.show', $data);
    }

    /**
     * Print the timetable.
     */
    public function print_record($ttr_id): View
    {
        $ttr = $this->tt->findTimetableRecord($ttr_id);
        $tms = $this->tt->getTimeSlotsByRecord($ttr_id);
        $tts = $this->tt->getTimeTableEntries(['ttr_id' => $ttr_id]);

        $days = $ttr->exam_id
            ? $tts->unique('exam_date')->pluck('exam_date')
            : $tts->unique('day')->pluck('day');
        $date_field = $ttr->exam_id ? 'exam_date' : 'day';

        $d_time = [];
        foreach ($days as $day) {
            foreach ($tms as $tm) {
                $entry = $tts->where('ts_id', $tm->id)->where($date_field, $day)->first();
                $d_time[] = [
                    'day' => $day,
                    'time' => $tm->full,
                    'subject' => $entry->subject->name ?? null,
                ];
            }
        }

        $data['ttr_id'] = $ttr_id;
        $data['ttr'] = $ttr;
        $data['my_class'] = $this->my_class->findClass($ttr->my_class_id);
        $data['time_slots'] = $tms;
        $data['tts'] = $tts;
        $data['days'] = $days;
        $data['d_time'] = collect($d_time);
        $data['settings'] = Qs::getSettings();

        if ($ttr->exam_id) {
            $data['exam_id'] = $ttr->exam_id;
            $data['exam'] = $this->exam->find($ttr->exam_id);
        }

        return view('pages.timetables.print', $data);
    }

    /**
     * Store a newly created timetable record.
     */
    public function store_record(TTRecordRequest $req): JsonResponse
    {
        $data = $req->only(['name', 'my_class_id']);
        $section_id = $req->section_id;
        $exam_id = $req->exam_id;

        $data['section_id'] = $section_id == 'all' ? null : $section_id;
        $data['exam_id'] = $exam_id == 'class' ? null : $exam_id;
        $data['year'] = $this->year;

        $this->tt->createTimetableRecord($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Update the specified timetable record.
     */
    public function update_record(TTRecordRequest $req, $id): JsonResponse
    {
        $data = $req->only(['name', 'my_class_id']);
        $section_id = $req->section_id;
        $data['section_id'] = $section_id == 'all' ? null : $section_id;

        $this->tt->updateTimetableRecord($id, $data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified timetable record.
     */
    public function delete_record($ttr_id): RedirectResponse
    {
        $this->tt->deleteTimetableRecord($ttr_id);

        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
