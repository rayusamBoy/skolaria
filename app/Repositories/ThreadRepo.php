<?php

namespace App\Repositories;

use App\Models\Thread;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ThreadRepo
{
    /**
     * Get all threads (latest first).
     */
    public function getAllLatest(): Builder|Thread
    {
        return Thread::latest('updated_at');
    }

    /**
     * Find a thread by ID.
     */
    public function find(int $id): ?Thread
    {
        return Thread::find($id);
    }

    /**
     * Find a thread or fail.
     */
    public function findOrFail(int $id): Thread
    {
        return Thread::findOrFail($id);
    }

    /**
     * Find a thread with trashed.
     */
    public function findWithTrashed(int $id): ?Thread
    {
        return Thread::withTrashed()->find($id);
    }

    /**
     * Find a thread with trashed or fail.
     */
    public function findWithTrashedOrFail(int $id): Thread
    {
        return Thread::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new thread.
     */
    public function create(array $data): Thread
    {
        return Thread::create($data);
    }

    /**
     * Delete a thread (soft delete).
     */
    public function destroy(int $id): bool
    {
        $thread = $this->find($id);
        if (!$thread) {
            return false;
        }
        // Using Cmgmyr\Messenger's destroy method (soft delete)
        $thread->destroy($id);
        return true;
    }

    /**
     * Mark thread as read for a user.
     */
    public function markAsRead(int $thread_id, int $user_id): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->markAsRead($user_id);
        }
    }

    /**
     * Get participant user IDs for a thread, excluding a specific user.
     */
    public function getParticipantUserIds(int $thread_id, ?int $exclude_user_id = null): array
    {
        $thread = $this->find($thread_id);
        return $thread ? $thread->participantsUserIds($exclude_user_id) : [];
    }

    /**
     * Get unread messages count for a user in a thread.
     */
    public function getUserUnreadMessagesCount(int $thread_id, int $user_id): int
    {
        $thread = $this->find($thread_id);
        return $thread ? $thread->userUnreadMessagesCount($user_id) : 0;
    }

    /**
     * Add participants to a thread.
     */
    public function addParticipants(int $thread_id, array $user_ids): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->addParticipant($user_ids);
        }
    }

    /**
     * Remove a participant from a thread.
     */
    public function removeParticipantByUserId(int $thread_id, int $user_id): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->removeParticipantByUserId($user_id, $thread_id);
        }
    }

    /**
     * Activate all participants in a thread.
     */
    public function activateAllParticipants(int $thread_id): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->activateAllParticipants();
        }
    }

    /**
     * Check if a thread exists (including trashed).
     */
    public function exists(int $id): bool
    {
        return Thread::withTrashed()->where('id', $id)->exists();
    }

    /**
     * Get participants of a thread with their user data.
     */
    public function getParticipantsWithUser(int $thread_id): Collection
    {
        return Participant::where('thread_id', $thread_id)
            ->with('user')
            ->get();
    }

    /**
     * Get threads for a specific user.
     */
    public function getForUser(int $user_id): Builder
    {
        return Thread::forUser($user_id)->latest('updated_at');
    }

    /**
     * Get threads for a user with new messages.
     */
    public function getForUserWithNewMessages(int $user_id): Builder
    {
        return Thread::forUserWithNewMessages($user_id)->latest('updated_at');
    }
}
