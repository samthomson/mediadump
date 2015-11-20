<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class DropboxFolder extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     * @property string folder
     */
    protected $table = 'dropbox_folders';


    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
