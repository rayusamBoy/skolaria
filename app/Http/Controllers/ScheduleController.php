<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Repositories\EventRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PermissionKit\Concerns\AuthorizesPermissions;

class ScheduleController extends Controller
{
    use AuthorizesPermissions;

    public function __construct(protected EventRepo $event) {}

    /**
     * Display a listing of events.
     */
    public function index(): View
    {
        $data['events'] = $this->event->getAllEvents();
        $data['selected'] = false;

        return view('pages.schedule.index', $data);
    }

    /**
     * Store a newly created event.
     */
    public function create_event(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['user_id'] = auth()->id();
        $this->event->create($data);

        return Qs::json('The event "'.$data['name'].'" added successfully.');
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit_event(int $event_id): View
    {
        $data['event'] = $this->event->getEventsByConditions(['id' => $event_id])->first();
        $data['events'] = $this->event->getAllEvents();
        $data['selected'] = true;

        return view('pages.schedule.index', $data);
    }

    /**
     * Update the specified event.
     */
    public function update_event(Request $request, int $event_id): JsonResponse
    {
        $this->event->update($event_id, $request->except('_token'));

        return Qs::jsonUpdateOk();
    }

    /**
     * Remove the specified event.
     */
    public function delete_event(Request $req): RedirectResponse
    {
        $this->event->delete($req->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }
}
