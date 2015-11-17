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

        $oMDState = new MediaDumpState;
        $oMDState->public = 0;
        $iTempUser = $oMDState->save();

        $oJson = json_decode(MediaDumpController::ping()->getContent());

        $oMDState->delete();

        $this->assertEquals($oJson->md_state, "setup");
    }
}
