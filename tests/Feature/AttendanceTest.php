<?php

namespace Tests\Feature;

use App\User;
use App\WorkTime;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Fake login user
     *
     * @param int $userId
     * @return void
     */
    public function loginUser(int $userId)
    {
        auth()->guard('web')->loginUsingId($userId);
        $token = auth()->guard('api')->fromUser(\App\User::find($userId));
        auth()->guard('api')->setToken($token);
        auth()->guard('api')->authenticate();
    }

    public function test_init_state_with_fresh_user()
    {
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        //make the test request to init_state
        $response = $this->json('GET', 'init_state');
        $content = json_decode($response->content(), true);

        dd($content);
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
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        $test = now();
        Carbon::setTestNow($test);

        //make the test request to to start_work
        $response = $this->json('POST', 'start_work', ['workStatus' => 'work']);
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($test->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals(null,$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(0,$todayTime['seconds']);
    }

    public function test_stop_work_after_three_hours_of_work()
    {
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        //set start time
        $start = new Carbon();
        Carbon::setTestNow($start);

        //make the test request to to start_work
        $response = $this->json('POST', 'start_work', ['workStatus' => 'work']);
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($start->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals(null,$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(0,$todayTime['seconds']);

        //set stop time after 3 hours
        $stop = $start->copy()->addHours(3);
        Carbon::setTestNow($stop);

        //make the stop_work request
        $response = $this->json('POST','stop_work');
        $content = json_decode($response->content(),true);

        //test the response was succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($start->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals($stop->toTimeString(),$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(3 * 60 * 60,$todayTime['seconds']);
    }


}
