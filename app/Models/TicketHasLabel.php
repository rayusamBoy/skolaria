<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketHasLabel extends Model
{
    protected $fillable = ['ticket_id', 'label_id'];
    
    public $timestamps = false;
}
