<?php

namespace App\Models;

use Iksaku\Laravel\MassUpdate\MassUpdatable;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TicketLabel extends Model
{
    use CentralConnection, MassUpdatable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * Get Tickets RelationShip
     */
    public function tickets()
    {
        return $this->hasManyThrough(
            Ticket::class,
            TicketHasLabel::class,
            'ticket_label_id',
            'id',
            'id',
            'ticket_id'
        );
    }
}
