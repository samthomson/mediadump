<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class MediaDumpState extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'mediadump';


    public function ownerUser()
    {
        return $this->hasOne('App\User', 'id', 'owner_user');
    }
}
