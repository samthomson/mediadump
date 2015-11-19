<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class DropboxToken extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     * @property string public
     */
    protected $table = 'dropbox_tokens';


    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
