<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class DropboxFile extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     * @property string public
     */
    protected $table = 'dropbox_files';


    public function dropboxFileSource()
    {
        return $this->belongsto('App\DropboxFolder', 'dropbox_folder_id', 'id');
    }
}
