<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers;
use App\Http\Controllers\MediaDumpController;
use App\Models\User;
use App\Models\Settings;

class ApplicationStateTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */



    public function testPingValidState()
    {
        //$oJson = json_decode(MediaDumpController::ping());

        $oJson = json_decode(MediaDumpController::ping()->getContent());

        $this->assertContains($oJson->md_state, ['empty', 'setup']);
    }

    public function testPingStateEmpty()
    {
        $oJson = json_decode(MediaDumpController::ping()->getContent());

        $this->assertEquals($oJson->md_state, "empty");
    }

    public function testPingStateSetUp()
    {
        //$oJson = json_decode(MediaDumpController::ping());

        MediaDumpController::setupApplication("test setup", "test@setup.app", "p", false);

        $oJson = json_decode(MediaDumpController::ping()->getContent());

        $oMDState = Settings::first();

        $oMDState->ownerUser()->delete();
        $oMDState->delete();


        $this->assertEquals($oJson->md_state, "setup");
    }

    public function testSetUp()
    {
        if(Settings::count() > 0)
            return false;

        MediaDumpController::setupApplication("test setup", "test@setup.app", "p", false);

        $oMDState = Settings::first();

        $sName = $oMDState->ownerUser->name;

        $oMDState->ownerUser->delete();
        $oMDState->delete();


        $this->assertEquals($sName, "test setup");

    } 
    public function testSingleStateOnly()
    {
        // the mediadump table should have only one row, it can't be 'setup' if already set up

        // set it up at least once
        MediaDumpController::setupApplication("test setup", "1@setup.app", "p", false);

        // attempty a second set up
        MediaDumpController::setupApplication("test setup", "2@setup.app", "p", false);

        // check the db just has one
        $this->assertEquals(Settings::count(), 1);
    }
}
