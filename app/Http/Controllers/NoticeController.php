<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Helpers\Qs;
use App\Http\Requests\Notice\NoticeCreate;
use App\Http\Requests\Notice\NoticeSetAsViewed;
use App\Http\Requests\Notice\NoticeUpdate;
use App\Models\Notice;
use App\Repositories\NoticeRepo;
use App\Repositories\UserRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NoticeController extends Controller
{
    protected string $year;

    public function __construct(protected NoticeRepo $notice, protected UserRepo $user)
    {
        $this->permissionMap = config('permissions.names.tenant.notices');
        parent::__construct();
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Display a listing of notices (admin sees all, others see own).
     */
    public function index(): View
    {
        $data['notices'] = $this->notice->getAllOrOwnNotices();
        $data['edit'] = false;

        return view('pages.notices.index', $data);
    }

    /**
     * Store a newly created notice.
     */
    public function store(NoticeCreate $req): JsonResponse
    {
        $data = $req->all();
        $data['creator_id'] = $data['editor_id'] = auth()->id();
        $this->notice->createNotice($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Update the specified notice.
     */
    public function update(NoticeUpdate $req, Notice $notice): JsonResponse
    {
        $data = $req->all();
        $data['editor_id'] = auth()->id();
        $notice->update($data);

        return Qs::jsonUpdateOk();
    }

    /**
     * Show the form for editing the specified notice.
     */
    public function edit(int $notice): View
    {
        $data['notice'] = $this->notice->getNoticesById($notice)->first();
        $data['notices'] = $this->notice->getAllOrOwnNotices();
        $data['edit'] = true;

        return view('pages.notices.index', $data);
    }

    /**
     * Mark a notice as viewed by the current user.
     */
    #[SkipPermission]
    public function mark_as_viewed(NoticeSetAsViewed $req): JsonResponse
    {
        $notice_id = $req->id;
        $viewers_ids = $this->notice->getNoticesById($notice_id)->value('viewers_ids');
        $viewers_ids[] = auth()->id();
        $this->notice->updateNotice($notice_id, ['viewers_ids' => $viewers_ids]);

        return Qs::json('updated');
    }

    /**
     * Remove the specified notice.
     */
    public function destroy(Notice $notice): RedirectResponse
    {
        $notice->delete();

        return back()->with('flash_success', __('msg.delete_ok'));
    }
}
