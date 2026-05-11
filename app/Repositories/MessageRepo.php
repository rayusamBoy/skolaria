<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;

class MessageRepo
{
    /**
     * Create a new message.
     *
     * @param array $data
     * @return Message
     */
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * Find a message by ID.
     *
     * @param int $id
     * @return Message|null
     */
    public function find(int $id): ?Message
    {
        return Message::find($id);
    }

    /**
     * Find a message or throw a 404 exception.
     *
     * @param int $id
     * @return Message
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Message
    {
        return Message::findOrFail($id);
    }

    /**
     * Permanently delete a message (including soft‑deleted ones).
     *
     * @param int $id
     * @return bool
     */
    public function forceDeleteMessage(int $id): bool
    {
        $message = Message::withTrashed()->find($id);
        return $message ? (bool) $message->forceDelete() : false;
    }

    /**
     * Get messages before a given ID for pagination (infinite scroll).
     *
     * @param int $thread_id
     * @param int $before_id
     * @param int $limit
     * @return Collection
     */
    public function getPreviousMessages(int $thread_id, int $before_id, int $limit = 5): Collection
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
     * Get a single trashed message with its relationships.
     *
     * @param int $id
     * @return Message|null
     */
    public function getTrashedMessageWithRelations(int $id): ?Message
    {
        return Message::onlyTrashed()
            ->with(['user', 'deletor', 'deletor.roles', 'user.roles'])
            ->find($id);
    }
}
