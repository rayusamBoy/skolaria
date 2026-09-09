<?php

namespace App\Models;

use Cmgmyr\Messenger\Models\Models;
use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread as OriginalThread;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Thread extends OriginalThread
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty();
    }

    /**
     * Messages relationship with trashed with user.
     *
     * @codeCoverageIgnore
     */
    public function messagesWithTrashed()
    {
        return $this->hasMany(Models::classname(Message::class), 'thread_id', 'id')->orderBy('created_at')->with(['user', 'deletor'])->withTrashed();
    }

    /**
     * Returns all the soft delted threads.
     */
    public static function getTrashed()
    {
        return Thread::onlyTrashed()->get();
    }

    /**
     * Restore a thread by id.
     */
    public static function restoreThread(int $id)
    {
        return Thread::where('id', $id)->restore();
    }

    /**
     * Delete a soft deleted thread by id.
     */
    public static function force_delete(int $id)
    {
        return Thread::where('id', $id)->forceDelete();
    }

    /**
     * Remove participants from thread by user id.
     *
     *
     * @return void
     */
    public function removeParticipantByUserId(mixed $userId, int $threadId)
    {
        Models::participant()->where('thread_id', $threadId)->where('user_id', $userId)->delete();
    }

    /**
     * Generates array of participant information.
     *
     * @param  mixed  $userId
     * @return string
     */
    public function participantsArray($userId = null, array $columns = ['name', 'id'])
    {
        $participantsTable = Models::table('participants');
        $usersTable = Models::table('users');
        $userPrimaryKey = Models::user()->getKeyName();
        $tablePrefix = $this->getConnection()->getTablePrefix();

        $columnString = implode(", ' ', $tablePrefix$usersTable.", $columns);
        $selectString = "$tablePrefix$usersTable.$columnString";

        $participantNames = $this->getConnection()->table($usersTable)
            ->join($participantsTable, "$usersTable.$userPrimaryKey", '=', "$participantsTable.user_id")
            ->where("$participantsTable.thread_id", $this->id)
            ->where("$participantsTable.deleted_at", '=', null)
            ->select($this->getConnection()->raw($selectString));

        if ($userId !== null) {
            $participantNames->where("$usersTable.$userPrimaryKey", '!=', $userId);
        }

        return $participantNames->get();
    }

    /**
     * See is the thread has any soft deleted participant.
     *
     * @param  int  $threadId
     * @return bool
     */
    public function hasDirtyParticipant($threadId)
    {
        $trashed = Participant::onlyTrashed()->where('thread_id', $threadId)->get();

        return count($trashed) > 0 ? true : false;
    }

    /**
     * Checks to see if a user is a current participant of the thread.
     *
     * @param  mixed  $userId
     * @return bool
     */
    public function isDirtyParticipant($userId)
    {
        $participants = $this->participants()->onlyTrashed()->where('user_id', '=', $userId);

        return $participants->count() > 0;
    }
}
