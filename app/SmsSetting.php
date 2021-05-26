<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    protected $table = 'sms_settings';

    protected $fillable = [
        'name',
        'username',
        'password',
        'flash',
        'healthCareCenterList_id',
        'status'
    ];

    protected $hidden = [
        'healthCareCenterList_id'
    ];
}
