<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor_Ticket extends Model
{
    protected $table = "doctor_tickets";
    
    protected $fillable = [
        'doctor_id',
        'subject',
        'ip',
        'message',
        'user_id',
        'flag'
    ];

}
