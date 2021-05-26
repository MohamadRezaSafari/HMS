<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class City extends Model
{
    protected $fillable = [
        'cityName',
        'state_id'
    ];


    public function expertise()
    {
        return $this->belongsToMany('App\Expertise');
    }


    public function states()
    {
        return $this->belongsToMany('App\State');
    }
}
