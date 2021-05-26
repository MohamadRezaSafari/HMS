<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketAnswer extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'answer'
    ];

    protected $hidden = [
        'ticket_id',
        'user_id',
    ];
}
