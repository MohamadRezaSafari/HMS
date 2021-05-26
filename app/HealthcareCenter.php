<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthcareCenter extends Model
{
    protected $fillable = [
        'healthcareCenterName',
        'state_id',
        'city_id'
    ];
}
