<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Events\MessageArrived;
use App\Helpers\Usr;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\StudentRecord;
use App\Models\Thread;
use App\Notifications\NewMessageArrived;
use App\Repositories\UserRepo;
use App\User;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class MessageController extends Controller
{
    protected $extra_messages_to_count, $user;
    public function __construct(UserRepo $user)
    {
        $this->extra_messages_to_count = 5;
        $this->user = $user;
    }

    /**
     * Show all of the message threads to the user.
     *
     * @return mixed
     */
    public function index()
    {
        // All threads, ignore deleted/archived participants
        $threads = Thread::getAllLatest()->get();

        // All threads that user is participating in
        // $threads = Thread::forUser(auth()->id())->latest('updated_at')->get();

        // All threads that user is participating in, with new messages
        // $threads = Thread::forUserWithNewMessages(auth()->id())->latest('updated_at')->get();

        $users = self::get_users();
        $user_roles = $this->user->getRoles();

        return view('messenger.index', compact('threads', 'users', 'user_roles'));
    }

    /**
     * Shows a message thread.
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $id = Usr::decodeHash($id);

        try {
            $thread = Thread::withTrashed()->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('messages.index')->with('flash_error', 'The thread was not found.');
        }

        // show current user in list if not a current participant
        // $users = User::whereNotIn('id', $thread->participantsUserIds())->get();

        // don't show the current user in list
        $user_id = auth()->id();
        $users = User::whereNotIn('id', $thread->participantsUserIds($user_id))->with(['roles'])->get();
        $extra_messages_to_count = $this->extra_messages_to_count;
        $unread_messages_count = $thread->userUnreadMessagesCount($user_id);

        $user_as_participant = Participant::where(['thread_id' => $thread->id, 'user_id' => $user_id])->get();

        $thread->markAsRead($user_id);

        return view('messenger.show', compact('thread', 'users', 'user_as_participant', 'extra_messages_to_count', 'unread_messages_count'));
    }

    /**
     * Get all users except the authenticated one.
     *
     * @return mixed
     */
    public function get_users()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return $users;
    }

    /**
     * Creates a new message thread.
     *
     * @return mixed
     */
    public function create()
    {
        $users = User::where('id', '!=', auth()->id())->get();

        return view('messenger.create', compact('users'));
    }

    /**
     * Stores a new message thread.
     *
     * @return mixed
     */
    public function store()
    {
        $input = Request::all();

        Validator::make($input, [
            'subject' => 'required|string|min:3|max:300',
            'message' => 'required|string|min:2|max:500',
            'recipients' => 'required_without:user_roles|array',
            'user_roles' => 'required_without:recipients|array'
        ])->validate();

        $thread = Thread::create([
            'subject' => $input['subject'],
        ]);

        // Message
        Message::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'body' => $input['message'],
        ]);

        // Sender
        Participant::create([
            'thread_id' => $thread->id,
            'user_id' => auth()->id(),
            'last_read' => new Carbon(),
        ]);

        // Recipients - individuals
        if (Request::has('recipients')) {
            $thread->addParticipant($input['recipients']);
        }

        // Recipients - by user role(s)
        if (Request::has('user_roles')) {
            foreach ($input['user_roles'] as $role) {
                // If active students
                $ids = match ($role) {
                    'students_active' => StudentRecord::where('grad', 0)->with('user')->get()->where('user.user_type', 'student')->pluck('user_id')->toArray(),
                    'students_graduated' => StudentRecord::where('grad', 1)->with('user')->get()->where('user.user_type', 'student')->pluck('user_id')->toArray(),
                    default => User::whereHas('roles', function ($query) use ($role) {
                            $query->where('name', $role);
                        })->get()->pluck('id')->toArray(),
                };

                $thread->addParticipant($ids);
            }
        }

        return redirect()->route('messages.index')->with('flash_success', __('msg.msg_thread_created'));
    }

    /**
     * Adds a new message to a current thread.
     *
     * @param $id
     * @return mixed
     */
    public function update($id)
    {
        $input = Request::all();

        Validator::make($input, [
            'message' => 'required|string|min:2|max:500',
            'recipients' => 'sometimes|array'
        ])->validate();

        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return Usr::json('The thread was not found.', false);
        }

        try {
            // Message
            $message = Message::create([
                'thread_id' => $thread->id,
                'user_id' => auth()->id(),
                'body' => Request::input('message'),
            ]);

            // Add replier as a participant
            $participant = Participant::firstOrCreate([
                'thread_id' => $thread->id,
                'user_id' => auth()->id(),
            ]);

            $participant->last_read = new Carbon();
            $participant->save();

            // Recipients
            if (Request::has('recipients'))
                $thread->addParticipant($input['recipients']);
        } catch (Throwable $e) {
            // Rollback the created message.
            $message->deleteMessageForever($message->id);
            Log::error("Error in MessageController@update while creating a message: " . $e->getMessage());
            return Usr::json(null, false, ['msg' => "Something went wrong. Cannot create a message.", 'ok' => false, 'complete' => true]);
        }

        try {
            $message = Message::with(['user', 'deletor', 'deletor.roles', 'user.roles'])->find($message->id);
            $users = Participant::where('thread_id', $thread->id)->with('user')->get()->where('user.id', '<>', auth()->id())->whereNotNull('user')->pluck('user');
            // Update user photo path to full asset url
            $message['user']['photo'] = Usr::getTenancyAwarePhoto($message['user']['photo']);

            Notification::sendNow($users, new NewMessageArrived($message));
            broadcast(new MessageArrived($message));

            return Usr::jsonWithMsgOnly('scrollToBtn');
        } catch (Throwable $e) {
            $message->deleteMessageForever($message->id);
            Log::error("Error in MessageController@update while sending a message: " . $e->getMessage());
            return Usr::json(null, false, ['msg' => "Something went wrong. Cannot send a message.", 'ok' => false, 'complete' => true]);
        }
    }

    /**
     * Update participant last read.
     *
     * @param int $thread_id
     * @return mixed
     */
    public function update_participant_last_read(int $thread_id)
    {
        $thread = Thread::withTrashed()->findOrFail($thread_id);
        $thread->markAsRead(auth()->id());
        return Usr::jsonUpdateOk();
    }

    /**
     * Delete a message thread.
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id)
    {
        try {
            $thread = Thread::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'The thread was not found.');
        }

        $thread->destroy($id);
        return redirect()->route('messages.index')->with('flash_success', __('msg.del_ok'));
    }

    /**
     * Remove Participant.
     *
     * @param int $thread_id
     * @param int $participant_id
     * @return mixed
     */
    public function remove_participant(int $thread_id, int $participant_id)
    {
        $thread_id = Usr::decodeHash($thread_id);
        $participant_id = Usr::decodeHash($participant_id);

        try {
            $thread = Thread::findOrFail($thread_id);
        } catch (ModelNotFoundException $e) {
            if (Thread::onlyTrashed()->find($thread_id)->exists())
                return redirect()->back()->with('flash_info', 'You must restore the thread first to remove the participant.');
            return redirect()->back()->with('flash_error', 'The thread was not found.');
        }

        $thread->removeParticipantByUserId($participant_id, $thread_id);
        return redirect()->back()->with('flash_success', 'Participant has been removed.');
    }

    /**
     * Delete a user creted message.
     *
     * @param int $msg_id
     * @return mixed
     */
    public function user_delete(int $msg_id)
    {
        try {
            $message = Message::findOrFail($msg_id);
        } catch (ModelNotFoundException $e) {
            return Usr::json('The message was not found.', FALSE);
        }

        try {
            $message->updateMessage($msg_id, ['deleted_by' => auth()->id()]);
            $message->deleteMessage($msg_id);
            $message = Message::onlyTrashed()->with(['user', 'deletor', 'deletor.roles', 'user.roles'])->find($message->id);
            // Update user photo path to full asset url
            $message['user']['photo'] = Usr::getTenancyAwarePhoto($message['user']['photo']);

            broadcast(new MessageDeleted($message));
            return Usr::jsonWithMsgOnly('complete');
        } catch (Throwable $e) {
            // Roll back message actions.
            $message->updateMessage($msg_id, ['deleted_by' => NULL]);
            $message->restoreDeletedMessage($msg_id);

            return Usr::json(null, TRUE, ['ok' => FALSE, 'msg' => "Sorry, something went wrong. Can't delete a message.", 'complete' => true]);
        }
    }

    /**
     * Delete a user creted message.
     *
     * @param int $thread_id
     * @param int $participant_id
     * @return mixed
     */
    public function activate_all_participants(int $thread_id)
    {
        $thread_id = Usr::decodeHash($thread_id);

        try {
            $thread = Thread::findOrFail($thread_id);
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('flash_error', 'The thread was not found.');
        }

        $thread->activateAllParticipants();
        return redirect()->back()->with('flash_success', 'Participants have been activated succussfully.');
    }

    /**
     * Load previous messages.
     *
     * @param int $thread_id
     * @param int $current_first_msg_id_in_view
     * @return mixed
     */
    public function load_previous(int $thread_id, int $current_first_msg_id_in_view)
    {
        $messages_string = '';
        $messages = Message::withTrashed()->with(['user', 'deletor', 'deletor.roles'])->where('thread_id', $thread_id)->where('id', '<', $current_first_msg_id_in_view)->get()->take(-$this->extra_messages_to_count);

        foreach ($messages as $message) {
            $data['message'] = $message;
            $messages_string .= view('messenger.partials.messages', $data)->render();
        }

        return Usr::json($messages_string);
    }
}
