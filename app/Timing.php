<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Timing extends Model
{
    protected $fillable = [
        'id',
		'start_time',
		'end_time',
		'start_date',
		'end_date',
		'doctor_id',
		'visitCount',
		'for',
        'confirm',
        'confirmTitle'
    ];

    public function days()
    {
        return $this->belongsToMany('App\Day');
    }

    public function getDayListAttribute()
    {
        return $this->days()->pluck('id')->all();
    }
}
