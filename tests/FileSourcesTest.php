<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers;
use App\Http\Controllers\MediaDumpController;
use App\Http\Controllers\FileSourcesController;

use App\User;
use App\Models\DropboxFolder;

class FileSourcesTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */


    /*
    test dropbox methods
    */

    public function testAddDropboxFolder()
    {
        $sFolderName = "test folder/path";

        $oUser = new User;
        $oUser->save();

        FileSourcesController::addDropboxFolderToUser($oUser, $sFolderName);


        $oFolders = $oUser->dropboxFolders()->where("folder", $sFolderName)->get();

        $this->assertEquals($oFolders[0]->folder, $sFolderName);
    }
    public function testCantAddSameFolderTwice()
    {
        $sFolderName = "test folder/path";

        $oUser = new User;
        $oUser->save();

        FileSourcesController::addDropboxFolderToUser($oUser, $sFolderName);

        FileSourcesController::addDropboxFolderToUser($oUser, $sFolderName);

        $this->assertEquals(1, count($oUser->dropboxFolders));
    }
}
