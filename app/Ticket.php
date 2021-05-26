<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'healthcareCenterList_id',
        'subject',
        'message',
        'ip',
        'flag'
    ];

    protected $hidden = [
        'user_id',
        'healthcareCenterList_id'
    ];
}
