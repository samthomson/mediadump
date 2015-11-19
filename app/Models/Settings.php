<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     * @property string public
     */
    protected $table = 'settings';


    public function ownerUser()
    {
        return $this->belongsto('App\User', 'owner_user', 'id');
    }
}
