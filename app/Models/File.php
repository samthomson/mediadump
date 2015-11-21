<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'files';


    public function user()
    {
        return $this->belongsto('App\User', 'user_id', 'id');
    }
    public function fileSourceType()
    {
        return $this->hasOne('App\FileSourceType', 'id', 'file_source_type_id');
    }
}
