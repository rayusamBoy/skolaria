<?php

namespace App\Http\Controllers;

use App\Auth\Attributes\SkipPermission;
use App\Events\MessageArrived;
use App\Events\MessageDeleted;
use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Models\StudentRecord;
use App\Notifications\NewMessageArrived;
use App\Repositories\MessageRepo;
use App\Repositories\ThreadRepo;
use App\Repositories\UserRepo;
use App\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class MessageController extends Controller
{
    protected int $extra_messages_to_count;
    protected UserRepo $user;
    protected ThreadRepo $thread;
    protected MessageRepo $message;

    public function __construct(UserRepo $user, ThreadRepo $thread, MessageRepo $message)
    {
        $this->extra_messages_to_count = 5;
        $this->user = $user;
        $this->thread = $thread;
        $this->message = $message;
    }

    /**
     * Show all message threads for the authenticated user.
     */
    public function index(): View
    {
        $threads = $this->thread->getThreadsOrderedByLatest()->get();
        $users = $this->getUsersExceptAuth();
        $user_roles = $this->user->getRolesByName();

        return view('messenger.index', compact('threads', 'users', 'user_roles'));
    }

    /**
     * Display a specific message thread.
     */
    public function show($id): View|RedirectResponse
    {
        $id = Usr::decodeHash($id);

        try {
            $thread = $this->thread->findWithTrashedOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('messages.index')->with('flash_error', 'The thread was not found.');
        }

        $user_id = auth()->id();
        $users = User::whereNotIn('id', $this->thread->getThreadParticipantIds($id, $user_id))
            ->with(['roles'])
            ->get();
        $extra_messages_to_count = $this->extra_messages_to_count;
        $unread_messages_count = $this->thread->getUnreadCountForUser($id, $user_id);
        $user_as_participant = Participant::where(['thread_id' => $id, 'user_id' => $user_id])->get();

        $this->thread->markThreadAsReadForUser($id, $user_id);

        return view('messenger.show', compact(
            'thread',
            'users',
            'user_as_participant',
            'extra_messages_to_count',
            'unread_messages_count'
        ));
    }

    /**
     * Get all users except the authenticated one.
     */
    public function get_users()
    {
        return User::where('id', '!=', auth()->id())->get();
    }

    /**
     * Show form to create a new message thread.
     */
    public function create(): View
    {
        $users = $this->getUsersExceptAuth();
        return view('messenger.create', compact('users'));
    }

    /**
     * Store a newly created message thread.
     */
    public function store(Request $request): RedirectResponse
    {
        $input = $request->all();

        Validator::make($input, [
            'subject' => 'required|string|min:3|max:300',
            'message' => 'required|string|min:2|max:500',
            'recipients' => 'required_without:user_roles|array',
            'user_roles' => 'required_without:recipients|array',
        ])->validate();

        $thread = $this->thread->create(['subject' => $input['subject']]);

        $this->message->create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'body' => $input['message'],
        ]);

        Participant::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'last_read' => new Carbon(),
        ]);

        if ($request->has('recipients')) {
            $this->thread->addParticipantsToThread($thread->id, $input['recipients']);
        }

        if ($request->has('user_roles')) {
            $this->addParticipantsByRole($thread, $input['user_roles']);
        }

        return redirect()->route('messages.index')->with('flash_success', __('msg.msg_thread_created'));
    }

    /**
     * Add a new message to an existing thread.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $input = $request->all();

        Validator::make($input, [
            'message' => 'required|string|min:2|max:500',
            'recipients' => 'sometimes|array',
        ])->validate();

        try {
            $thread = $this->thread->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return Usr::json('The thread was not found.', false);
        }

        try {
            $message = $this->createMessageAndUpdateParticipant($thread, $input);
        } catch (Throwable $e) {
            Log::error("Error in MessageController@update while creating a message: " . $e->getMessage());
            return Usr::json(null, false, [
                'msg' => 'Something went wrong. Cannot create a message.',
                'ok' => false,
                'complete' => true,
            ]);
        }

        try {
            $this->broadcastNewMessage($message, $thread);
            return Usr::jsonWithSingleKey('scrollToBtn');
        } catch (Throwable $e) {
            $this->message->forceDeleteMessage($message->id);
            Log::error("Error in MessageController@update while sending a message: " . $e->getMessage());
            return Usr::json(null, false, [
                'msg' => 'Something went wrong. Cannot send a message.',
                'ok' => false,
                'complete' => true,
            ]);
        }
    }

    /**
     * Update participant's last read timestamp.
     */
    #[SkipPermission]
    public function update_participant_last_read(int $thread_id): JsonResponse
    {
        $this->thread->markThreadAsReadForUser($thread_id, auth()->id());
        return Usr::jsonUpdateOk();
    }

    /**
     * Delete a message thread.
     */
    public function destroy($id): RedirectResponse
    {
        try {
            $thread = $this->thread->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'The thread was not found.');
        }

        $thread->delete();
        return redirect()->route('messages.index')->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Remove a participant from a thread.
     */
    public function remove_participant(int $thread_id, int $participant_id): RedirectResponse
    {
        $thread_id = Usr::decodeHash($thread_id);
        $participant_id = Usr::decodeHash($participant_id);

        try {
            $this->thread->findOrFail($thread_id);
        } catch (ModelNotFoundException $e) {
            if ($this->thread->exists($thread_id)) {
                return redirect()->back()->with('flash_info', 'You must restore the thread first to remove the participant.');
            }
            return redirect()->back()->with('flash_error', 'The thread was not found.');
        }

        $this->thread->removeParticipantFromThread($thread_id, $participant_id);
        return redirect()->back()->with('flash_success', 'Participant has been removed.');
    }

    /**
     * Soft-delete a user-created message.
     */
    public function user_delete(int $msg_id): JsonResponse
    {
        try {
            $message = $this->message->findOrFail($msg_id);
        } catch (ModelNotFoundException $e) {
            return Usr::json('The message was not found.', false);
        }

        try {
            $message->deleted_by = auth()->id();
            $message->save();
            $message->delete();

            $deleted_message = $this->message->getTrashedMessageWithRelations($msg_id);
            $message_html = view('messenger.partials.messages', ['message' => $deleted_message])->render();

            broadcast(new MessageDeleted($deleted_message, $message_html));
            return Usr::jsonWithSingleKey('complete');
        } catch (Throwable $e) {
            $message->update(['deleted_by' => null]);
            $message->restore();

            return Usr::json(arr: [
                'ok' => false,
                'msg' => "Sorry, something went wrong. Can't delete the message.",
                'complete' => true,
            ]);
        }
    }

    /**
     * Activate all participants in a thread.
     */
    public function activate_all_participants(int $thread_id): RedirectResponse
    {
        $thread_id = Usr::decodeHash($thread_id);

        try {
            $this->thread->findOrFail($thread_id);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('flash_error', 'The thread was not found.');
        }

        $this->thread->activateAllThreadParticipants($thread_id);
        return redirect()->back()->with('flash_success', 'Participants have been activated successfully.');
    }

    /**
     * Load previous messages via AJAX.
     */
    public function load_previous(int $thread_id, int $current_first_msg_id_in_view): JsonResponse
    {
        $messages = $this->message->getPreviousMessages(
            $thread_id,
            $current_first_msg_id_in_view,
            $this->extra_messages_to_count
        );

        $messages_html = '';
        foreach ($messages as $msg) {
            $messages_html .= view('messenger.partials.messages', ['message' => $msg])->render();
        }

        return Usr::json($messages_html);
    }

    // ----------------------------------------------------------------------
    // Protected Helper Methods
    // ----------------------------------------------------------------------

    /**
     * Get all users except the authenticated one.
     */
    protected function getUsersExceptAuth()
    {
        return User::where('id', '!=', auth()->id())->get();
    }

    /**
     * Add participants to thread based on role names.
     */
    protected function addParticipantsByRole($thread, array $roles): void
    {
        foreach ($roles as $role) {
            $user_ids = match ($role) {
                'students_active' => StudentRecord::where('is_graduated', false)
                    ->with('user')
                    ->get()
                    ->where('user.user_type', 'student')
                    ->pluck('user_id')
                    ->toArray(),
                'students_graduated' => StudentRecord::where('is_graduated', true)
                    ->with('user')
                    ->get()
                    ->where('user.user_type', 'student')
                    ->pluck('user_id')
                    ->toArray(),
                default => User::whereHas('roles', fn($q) => $q->where('name', $role))
                    ->pluck('id')
                    ->toArray(),
            };

            if (!empty($user_ids)) {
                $this->thread->addParticipantsToThread($thread->id, $user_ids);
            }
        }
    }

    /**
     * Create a new message and update/add participant.
     */
    protected function createMessageAndUpdateParticipant($thread, array $input)
    {
        $message = $this->message->create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'body' => $input['message'],
        ]);

        $participant = Participant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
        ]);
        $participant->last_read = new Carbon();
        $participant->save();

        if (isset($input['recipients'])) {
            $this->thread->addParticipantsToThread($thread->id, $input['recipients']);
        }

        return $message;
    }

    /**
     * Broadcast new message and send notifications.
     */
    protected function broadcastNewMessage($message, $thread): void
    {
        $message->load(['user', 'deletor', 'deletor.roles', 'user.roles']);
        $message_html = view('messenger.partials.messages', ['message' => $message])->render();

        $recipients = Participant::where('thread_id', $thread->id)
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter(fn($u) => $u && $u->id !== auth()->id());

        Notification::sendNow($recipients, new NewMessageArrived($message));
        broadcast(new MessageArrived($message, $message_html));
    }
}
