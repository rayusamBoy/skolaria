<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketHasLabel;
use App\Models\TicketLabel;
use App\Models\TicketMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TicketRepo
{
    /*********** Tickets ***************/

    /**
     * Get all tickets.
     *
     * @return Collection
     */
    public function getAllTickets(): Collection
    {
        return Ticket::all();
    }

    /**
     * Find a ticket by its primary key.
     *
     * @param int $id
     * @return Ticket|null
     */
    public function findTicket(int $id): ?Ticket
    {
        return Ticket::find($id);
    }

    /**
     * Create a new ticket.
     *
     * @param array $attributes
     * @return Ticket
     */
    public function createTicket(array $attributes): Ticket
    {
        return Ticket::create($attributes);
    }

    /**
     * Delete a ticket by ID.
     *
     * @param int $id
     * @return int  Number of deleted rows (0 or 1)
     */
    public function deleteTicket(int $id): int
    {
        return Ticket::destroy($id);
    }

    /**
     * Get tickets matching the given conditions.
     *
     * @param array $where
     * @return Collection
     */
    public function getTicketsByConditions(array $where): Collection
    {
        return Ticket::where($where)->get();
    }

    /**
     * Update ticket(s) matching an ID.
     *
     * @param int   $id
     * @param array $attributes
     * @return int  Number of updated rows
     */
    public function updateTicket(int $id, array $attributes): int
    {
        return Ticket::where('id', $id)->update($attributes);
    }

    /*********** Ticket Messages ***************/

    /**
     * Create a new ticket message.
     *
     * @param array $attributes
     * @return TicketMessage
     */
    public function createMessage(array $attributes): TicketMessage
    {
        return TicketMessage::create($attributes);
    }

    /**
     * Query builder for ticket messages matching conditions.
     *
     * @param array $where
     * @return Builder|TicketMessage
     */
    public function getMessagesQuery(array $where): Builder|TicketMessage
    {
        return TicketMessage::where($where);
    }

    /*********** Categories ***************/

    /**
     * Get all ticket categories.
     *
     * @return Collection
     */
    public function getCategories(): Collection
    {
        return TicketCategory::all();
    }

    /**
     * Update or create a ticket category.
     *
     * @param array $attributes
     * @param array $values
     * @return TicketCategory
     */
    public function updateOrCreateCategory(array $attributes, array $values): TicketCategory
    {
        return TicketCategory::updateOrCreate($attributes, $values);
    }

    /**
     * Delete category(ies) where a column is not in a given list.
     *
     * @param string $column
     * @param array  $values
     * @return int
     */
    public function deleteCategoryWhereNotIn(string $column, array $values): int
    {
        return TicketCategory::whereNotIn($column, $values)->delete();
    }

    /*********** Labels ***************/

    /**
     * Get all ticket labels.
     *
     * @return Collection
     */
    public function getLabels(): Collection
    {
        return TicketLabel::all();
    }

    /**
     * Update or create a ticket label.
     *
     * @param array $attributes
     * @param array $values
     * @return TicketLabel
     */
    public function updateOrCreateLabel(array $attributes, array $values): TicketLabel
    {
        return TicketLabel::updateOrCreate($attributes, $values);
    }

    /**
     * Delete label(s) where a column is not in a given list.
     *
     * @param string $column
     * @param array  $values
     * @return int
     */
    public function deleteLabelWhereNotIn(string $column, array $values): int
    {
        return TicketLabel::whereNotIn($column, $values)->delete();
    }

    /*********** Ticket <-> Label pivot ***************/

    /**
     * Assign labels to a ticket by inserting rows in the pivot table.
     * Note: This does **not** remove existing labels first.
     *
     * @param int   $ticket_id
     * @param array $label_ids
     * @return bool
     */
    public function assignLabelsToTicket(int $ticket_id, array $label_ids): bool
    {
        $values = [];
        foreach ($label_ids as $labelId) {
            $values[] = [
                'ticket_id' => $ticket_id,
                'label_id' => $labelId,
            ];
        }
        return TicketHasLabel::insert($values);
    }
}
