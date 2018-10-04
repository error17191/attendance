<?php

namespace Tests\Feature;

use App\User;
use App\WorkTime;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_init_state_with_fresh_user()
    {
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        Artisan::call('seed:users');

        //enable fake authentication
        config(['app.enable_fake_login' => true]);
        config(['app.fake_logged_user' => 11]);

        //make the test request to init_state
        $response = $this->json('GET', 'init_state');
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200, $response->status());
        $this->assertCount(5, $content);

        //test the flags field
        $flags = $content['flags'];
        $actualFlags = app('settings')->getFlags();
        $this->assertCount(count($actualFlags), $flags);
        foreach ($flags as $flag) {
            $this->assertTrue(isset($actualFlags[$flag['type']]));
            $this->assertEquals($actualFlags[$flag['type']] * 60 * 60, $flag['limitSeconds']);
            $this->assertEquals($actualFlags[$flag['type']] * 60 * 60, $flag['remainingSeconds']);
            $this->assertTrue(!$flag['inUse']);
        }

        //test the work time signs field
        $this->assertCount(0, $content['workTimeSigns']);

        //test the status field
        $this->assertEquals('off', $content['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertEquals(0, $todayTime['seconds']);
        $this->assertEquals('', $todayTime['workStatus']);

        //test the month report
        $monthReport = $content['month_report'];
        $this->assertEquals(0, $monthReport['actual']['seconds']);
        $this->assertEquals($monthReport['ideal']['seconds'], $monthReport['diff']['seconds']);
        $this->assertEquals('less', $monthReport['diff']['type']);
    }

    public function test_start_first_work_time()
    {
        Artisan::call('seed:settings');
        Artisan::call('seed:users');

        $this->loginUser(1);

        //make the test request to to start_work
        $response = $this->json('POST', 'start_work', ['workStatus' => 'work']);
        $content = json_decode($response->content(), true);
        //test that response succeeded
        $this->assertEquals(200,$response->status());
    }

    public function loginUser($userId)
    {
        auth()->guard('web')->loginUsingId($userId);
        $token = auth()->guard('api')->fromUser(\App\User::find($userId));
        auth()->guard('api')->setToken($token);
        auth()->guard('api')->authenticate();
    }
}
