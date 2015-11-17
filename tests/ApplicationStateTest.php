<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers;
use App\Http\Controllers\MediaDumpController;
use App\Models\User;
use App\Models\MediaDumpState;

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

        $oMDState = MediaDumpState::first();

        $oMDState->OwnerUser->delete();
        $oMDState->delete();

        
        $this->assertEquals($oJson->md_state, "setup");
    }

    public function testSetUp()
    {
        if(MediaDumpState::count() > 0)
            return false;

        MediaDumpController::setupApplication("test setup", "test@setup.app", "p", false);

        $oMDState = MediaDumpState::first();

        $sName = $oMDState->OwnerUser->name;

        $oMDState->OwnerUser->delete();
        $oMDState->delete();


        $this->assertEquals($sName, "test setup");

    } 
}
