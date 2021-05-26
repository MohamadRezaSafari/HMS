<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor_Ticket_Answer extends Model
{
    protected $table = "doctor_ticket_answers";

    protected $fillable = [
        'doctor_ticket_id',
        'user_id',
        'read',
        'answer'
    ];
}
