<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PoliclinicQueue extends Model
{
    protected $fillable = [
        'doctor_id',
        'nationalCode',
        'timings_id',
        'name',
        'mobile',
        'ip',
        'forDate',
        'innings',
        'trackingCode',
        'transId',
        'amount',
        'cardNumber',
        'returnAmount',
        'flag'
    ];
}
