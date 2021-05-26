<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'doctorName',
        'doctorLastName',
        'expertise_id',
        'doctorTell',
        'academicRank',
        'doctorAddress',
        'doctorTime',
        'doctorImg',
        'state_id',
        'city_id',
        'healthcareCenterList_id',
        'fellowship',
        'graduateFrom',
        'expertiseField',
        'specialty',
        'doctorOnlineVisitStatus',
        'clinicStatus',
        'clinicName',
        'clinicTell',
        'clinicAddress',
        'confirm',
        'confirmTitle',
        'clinicVisitPrice',
        'hospitalVisitPrice'
    ];
}
