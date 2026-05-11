<?php

namespace App\Repositories;

use App\Models\Thread;
use Illuminate\Database\Eloquent\Builder;

class ThreadRepo
{
    /**
     * Get a query builder for all threads ordered by latest update.
     *
     * @return Builder
     */
    public function getThreadsOrderedByLatest(): Builder|Thread
    {
        return Thread::latest('updated_at');
    }

    /**
     * Find a thread by ID.
     *
     * @param int $id
     * @return Thread|null
     */
    public function find(int $id): ?Thread
    {
        return Thread::find($id);
    }

    /**
     * Find a thread or throw a 404 exception.
     *
     * @param int $id
     * @return Thread
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Thread
    {
        return Thread::findOrFail($id);
    }

    /**
     * Find a thread with trashed or throw a 404.
     *
     * @param int $id
     * @return Thread
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findWithTrashedOrFail(int $id): Thread
    {
        return Thread::withTrashed()->findOrFail($id);
    }

    /**
     * Create a new message thread.
     *
     * @param array $data
     * @return Thread
     */
    public function create(array $data): Thread
    {
        return Thread::create($data);
    }

    /**
     * Mark all messages in a thread as read for a user.
     *
     * @param int $thread_id
     * @param int $user_id
     * @return void
     */
    public function markThreadAsReadForUser(int $thread_id, int $user_id): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->markAsRead($user_id);
        }
    }

    /**
     * Get participant user IDs for a thread, optionally excluding a user.
     *
     * @param int      $thread_id
     * @param int|null $exclude_user_id
     * @return array
     */
    public function getThreadParticipantIds(int $thread_id, ?int $exclude_user_id = null): array
    {
        $thread = $this->find($thread_id);
        return $thread ? $thread->participantsUserIds($exclude_user_id) : [];
    }

    /**
     * Get the number of unread messages for a user in a thread.
     *
     * @param int $thread_id
     * @param int $user_id
     * @return int
     */
    public function getUnreadCountForUser(int $thread_id, int $user_id): int
    {
        $thread = $this->find($thread_id);
        return $thread ? $thread->userUnreadMessagesCount($user_id) : 0;
    }

    /**
     * Add participants to a thread.
     *
     * @param int   $thread_id
     * @param array $user_ids
     * @return void
     */
    public function addParticipantsToThread(int $thread_id, array $user_ids): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->addParticipant($user_ids);
        }
    }

    /**
     * Remove a participant from a thread by user ID.
     *
     * @param int $thread_id
     * @param int $user_id
     * @return void
     */
    public function removeParticipantFromThread(int $thread_id, int $user_id): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->removeParticipantByUserId($user_id, $thread_id);
        }
    }

    /**
     * Activate all participants (un‑deactivate them) in a thread.
     *
     * @param int $thread_id
     * @return void
     */
    public function activateAllThreadParticipants(int $thread_id): void
    {
        $thread = $this->find($thread_id);
        if ($thread) {
            $thread->activateAllParticipants();
        }
    }

    /**
     * Check if a thread exists (including soft‑deleted).
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return Thread::withTrashed()->where('id', $id)->exists();
    }
}
