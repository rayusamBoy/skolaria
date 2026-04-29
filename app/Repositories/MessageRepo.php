<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageRepo
{
    /**
     * Create a new message.
     */
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * Find a message by ID.
     */
    public function find(int $id): ?Message
    {
        return Message::find($id);
    }

    /**
     * Find a message or fail.
     */
    public function findOrFail(int $id): Message
    {
        return Message::findOrFail($id);
    }

    /**
     * Find a message with relationships.
     */
    public function findWith(int $id, array $with = []): ?Message
    {
        return Message::with($with)->find($id);
    }

    /**
     * Soft delete a message.
     */
    public function deleteMessage(int $id): bool
    {
        $message = $this->find($id);
        return $message ? $message->delete() : false;
    }

    /**
     * Permanently delete a message.
     */
    public function deleteMessageForever(int $id): bool
    {
        $message = Message::withTrashed()->find($id);
        return $message ? $message->forceDelete() : false;
    }

    /**
     * Update a message's attributes.
     */
    public function update(int $id, array $data): bool
    {
        $message = $this->find($id);
        return $message ? $message->update($data) : false;
    }

    /**
     * Restore a soft-deleted message.
     */
    public function restore(int $id): bool
    {
        $message = Message::onlyTrashed()->find($id);
        return $message ? $message->restore() : false;
    }

    /**
     * Get messages before a given ID (for pagination).
     */
    public function getMessagesBeforeId(int $thread_id, int $before_id, int $limit = 5): Collection
    {
        return Message::withTrashed()
            ->with(['user', 'deletor', 'deletor.roles'])
            ->where('thread_id', $thread_id)
            ->where('id', '<', $before_id)
            ->latest('id')
            ->take($limit)
            ->get();
    }

    /**
     * Get only trashed message with relations.
     */
    public function getTrashedWithRelations(int $id): ?Message
    {
        return Message::onlyTrashed()
            ->with(['user', 'deletor', 'deletor.roles', 'user.roles'])
            ->find($id);
    }

    /**
     * Delete all messages in a thread.
     */
    public function deleteByThreadId(int $thread_id): int
    {
        return Message::where('thread_id', $thread_id)->delete();
    }
}
