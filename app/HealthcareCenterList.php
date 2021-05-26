<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HealthcareCenterList extends Model
{
    protected $fillable = [
        'healthcareCenterListName',
		'healthcareCenter_id',
		'tell',
		'address',
		'expertise',
		'description',
		'map',
		'onlineVisitStatus',
		'img',
        'city_id',
        'state_id',
		'private',
        'trust'
    ];



}
