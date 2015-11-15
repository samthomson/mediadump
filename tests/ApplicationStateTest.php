<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Http\Controllers;
use App\Http\Controllers\MediaDumpController;
use App\User;

class ApplicationStateTest extends TestCase
{
    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testPingStateEmpty()
    {
        //$oJson = json_decode(MediaDumpController::ping());

        $oJson = json_decode(MediaDumpController::ping()->getContent());

        $this->assertEquals($oJson->md_state, "empty");
    }

    public function testPingStateSetUp()
    {
        //$oJson = json_decode(MediaDumpController::ping());

        $oUser = new User;
        $oUser->name = "test";
        $oUser->email = "test@test.test";
        $oUser->password = "testpass";
        $iTempUser = $oUser->save();

        $oJson = json_decode(MediaDumpController::ping()->getContent());

        $oUser->delete();

        $this->assertEquals($oJson->md_state, "setup");
    }
}
