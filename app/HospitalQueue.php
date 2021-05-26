<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HospitalQueue extends Model
{
    protected $fillable = [
    	'hospital_id',
        'doctor_id',
        'timings_id',
        'nationalCode',
        'name',
        'mobile',
        'ip',
        'forDate',
        'innings',
        'trackingCode',
        'sms_status',
        'flag',
        'transId',
        'amount',
        'cardNumber',
        'returnAmount'
    ];
}
