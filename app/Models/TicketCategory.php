<?php

namespace App\Models;

use Iksaku\Laravel\MassUpdate\MassUpdatable;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class TicketCategory extends Model
{
    use CentralConnection, MassUpdatable;

    protected $fillable = ['name', 'description', 'is_visible'];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

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
        return $this->belongsToMany(Ticket::class);
    }
}
