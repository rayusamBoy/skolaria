<?php

namespace App\Repositories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
     * Find a message or throw a 404 exception.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id): Message
    {
        return Message::findOrFail($id);
    }

    /**
     * Permanently delete a message (including soft‑deleted ones).
     */
    public function forceDeleteMessage(int $id): bool
    {
        $message = Message::withTrashed()->find($id);

        return $message ? (bool) $message->forceDelete() : false;
    }

    /**
     * Get messages before a given ID for pagination (infinite scroll).
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
     */
    public function getTrashedMessageWithRelations(int $id): ?Message
    {
        return Message::onlyTrashed()
            ->with(['user', 'deletor', 'deletor.roles', 'user.roles'])
            ->find($id);
    }
}
