<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsPropertyHealthCareCenterList extends Model
{

    protected $table = 'smsproperty_healthcarecenterlist';


    protected $fillable = [
        'smsProperty_id',
        'healthcareCenterList_id',
        'smsSetting_id',
        'status',
        'value',
        'sms_message'
    ];

}
