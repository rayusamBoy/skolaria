<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketHasLabel;
use App\Models\TicketLabel;
use App\Models\TicketMessage;

class TicketRepo
{
    // Tickets
    public function getAll()
    {
        return Ticket::all();
    }

    public function findTicket($id)
    {
        return Ticket::find($id);
    }

    public function createTicket($attributes)
    {
        return Ticket::create($attributes);
    }

    public function deleteTicket($id)
    {
        return Ticket::destroy($id);
    }

    public function getTickets($where)
    {
        return Ticket::where($where)->get();
    }

    public function updateTicket($id, $attributes)
    {
        return Ticket::where('id', $id)->update($attributes);
    }

    // Ticket messages
    public function createMessage($attributes)
    {
        return TicketMessage::create($attributes);
    }

    public function whereMessages($where)
    {
        return TicketMessage::where($where);
    }

    // Categories
    public function getCategories()
    {
        return TicketCategory::all();
    }

    public function updateOrCreateCategory(array $attributes, array $values)
    {
        return TicketCategory::updateOrCreate($attributes, $values);
    }

    public function deleteCategoryWhereNotIn(string $column, array $values)
    {
        return TicketCategory::whereNotIn($column, $values)->delete();
    }

    // Labels
    public function getLabels()
    {
        return TicketLabel::all();
    }

    public function updateOrCreateLabel(array $attributes, array $values)
    {
        return TicketLabel::updateOrCreate($attributes, $values);
    }

    public function deleteLabelWhereNotIn(string $column, array $values)
    {
        return TicketLabel::whereNotIn($column, $values)->delete();
    }

    // TicketHasLabel
    public function assignLabelsToTicket($ticket_id, array $label_ids)
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
