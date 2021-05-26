<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HospitalSms extends Model
{
    protected $table = 'hospital_sms';

    protected $fillable = [
        'hospital_id',
        'queue_id',
        'mobiles',
        'rec_id',
        'delivery_status'
    ];

    public $timestamps = false;
}
