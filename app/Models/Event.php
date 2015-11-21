<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'events';


    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function eventType()
    {
        return $this->hasOne('App\EventType', 'id', 'event_type_id');
    }
}
