<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers;
use App\Http\Controllers\MediaDumpController;

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

        $oDropboxFolder = new DropboxFolder;
        $oDropboxFolder->folder = $sFolderName;
        $oUser->save();

        $oUser->dropboxFolders()->save($oDropboxFolder);

        $oFolders = $oUser->dropboxFolders()->where("folder", $sFolderName)->get();

        $this->assertContains($oFolders[0]->folder, $sFolderName);
    }
/*    public function testCantAddSameFolderTwice()
    {
        //$oJson = json_decode(MediaDumpController::ping());

        $oJson = json_decode(MediaDumpController::ping()->getContent());

        $this->assertContains($oJson->md_state, ['empty', 'setup']);
    }*/
}
