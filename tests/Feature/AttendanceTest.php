<?php

namespace Tests\Feature;

use App\Flag;
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
        app('settings')->refreshData();
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

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
            $this->assertEquals($actualFlags[$flag['type']], $flag['limitSeconds']);
            $this->assertEquals($actualFlags[$flag['type']], $flag['remainingSeconds']);
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
        app('settings')->refreshData();
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        $test = now()->hour(12);
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
        app('settings')->refreshData();
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        //set start time
        $start = (new Carbon())->hour(12);
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

    /**
     * Test starting work then starting lost time flag after 2 hours of work
     * and ended the lost time flag after 20 minutes and stop work after 5 hours
     * of starting work
     */
    public function test_use_lost_time_flag_within_the_time_limit()
    {
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        app('settings')->refreshData();
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        //set start work time
        $startWork = (new Carbon())->hour(12);
        Carbon::setTestNow($startWork);

        //make the test request to to start_work
        $response = $this->json('POST', 'start_work', ['workStatus' => 'work']);
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($startWork->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals(null,$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(0,$todayTime['seconds']);

        //set start lost time flag
        $startFlag = $startWork->copy()->addHours(2);
        Carbon::setTestNow($startFlag);

        //make the test request to start flag
        $response = $this->json('POST','/flag/start',['type' => 'lost_time']);
        $content = json_decode($response->content(),true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the returned message
        $this->assertEquals('user started using lost_time flag',$content['message']);

        //test the started flag
        $this->assertEquals('on',User::find(1)->flag);
        $this->assertCount(1,Flag::all());
        $this->assertEquals($startFlag,Flag::first()->started_at);
        $this->assertEquals(null,Flag::first()->stopped_at);
        $this->assertEquals(0,Flag::first()->seconds);
        $this->assertEquals(1,Flag::first()->user_id);
        $this->assertEquals(1,Flag::first()->work_time_id);
        $this->assertEquals($startFlag->toDateString(),Flag::first()->day);

        //set stop flag time
        $stopFlag = $startFlag->copy()->addMinutes(20);
        Carbon::setTestNow($stopFlag);

        //make stop flag test request
        $response = $this->json('POST','/flag/end');

        //test that response succeeded
        $this->assertEquals(200,$response->status());

        //test the stopped flag
        $this->assertEquals('off',User::find(1)->flag);
        $this->assertCount(1,Flag::all());
        $this->assertEquals($startFlag,Flag::first()->started_at);
        $this->assertEquals($stopFlag,Flag::first()->stopped_at);
        $this->assertEquals(20 * 60,Flag::first()->seconds);
        $this->assertEquals(1,Flag::first()->user_id);
        $this->assertEquals(1,Flag::first()->work_time_id);
        $this->assertEquals($startFlag->toDateString(),Flag::first()->day);

        //set stop work time
        $stopWork = $startWork->copy()->addHours(5);
        Carbon::setTestNow($stopWork);

        //make the stop_work request
        $response = $this->json('POST','stop_work');
        $content = json_decode($response->content(),true);

        //test the response was succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($startWork->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals($stopWork->toTimeString(),$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(5 * 60 * 60,$todayTime['seconds']);

        //make init state test request to check the final states
        $response = $this->json('GET', 'init_state');
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200, $response->status());
        $this->assertCount(5, $content);

        //test the flags field
        $flag = $content['flags'][0];
        $lostTime = app('settings')->getFlags()['lost_time'];
        $this->assertEquals($lostTime, $flag['limitSeconds']);
        $this->assertEquals($lostTime - Flag::first()->seconds, $flag['remainingSeconds']);
        $this->assertTrue(!$flag['inUse']);

        //test the work time signs field
        $workTimeSigns = $content['workTimeSigns'];
        $this->assertCount(1, $workTimeSigns);
        $this->assertEquals($startWork->toTimeString(),$workTimeSigns[0]['started_at']);
        $this->assertEquals($stopWork->toTimeString(),$workTimeSigns[0]['stopped_at']);
        $this->assertEquals('work',$workTimeSigns[0]['status']);

        //test the status field
        $this->assertEquals('off', $content['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertEquals(5 * 60 *60, $todayTime['seconds']);
        $this->assertEquals('work', $todayTime['workStatus']);

        //test the month report
        $monthReport = $content['month_report'];
        $this->assertEquals(5 * 60 * 60, $monthReport['actual']['seconds']);
        $this->assertEquals($monthReport['ideal']['seconds'] - (5 * 60 * 60), $monthReport['diff']['seconds']);
        $this->assertEquals('less', $monthReport['diff']['type']);
    }

    /**
     * Test starting work then starting lost time flag after 2 hours of work
     * and ended the lost time flag after 90 minutes and means 60 minutes past the
     * flag time limit
     */
    public function test_stop_lost_time_flag_after_the_limit()
    {
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        app('settings')->refreshData();
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        //set start work time
        $startWork = (new Carbon())->hour(12);
        Carbon::setTestNow($startWork);

        //make the test request to to start_work
        $response = $this->json('POST', 'start_work', ['workStatus' => 'work']);
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($startWork->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals(null,$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(0,$todayTime['seconds']);

        //set start lost time flag
        $startFlag = $startWork->copy()->addHours(2);
        Carbon::setTestNow($startFlag);

        //make the test request to start flag
        $response = $this->json('POST','/flag/start',['type' => 'lost_time']);
        $content = json_decode($response->content(),true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the returned message
        $this->assertEquals('user started using lost_time flag',$content['message']);

        //test the started flag
        $this->assertEquals('on',User::find(1)->flag);
        $this->assertCount(1,Flag::all());
        $this->assertEquals($startFlag,Flag::first()->started_at);
        $this->assertEquals(null,Flag::first()->stopped_at);
        $this->assertEquals(0,Flag::first()->seconds);
        $this->assertEquals(1,Flag::first()->user_id);
        $this->assertEquals(1,Flag::first()->work_time_id);
        $this->assertEquals($startFlag->toDateString(),Flag::first()->day);

        //set stop flag time
        $stopFlag = $startFlag->copy()->addMinutes(90);
        Carbon::setTestNow($stopFlag);

        //make stop flag test request
        $response = $this->json('POST','/flag/end');

        //test that response succeeded
        $this->assertEquals(200,$response->status());

        //test the stopped flag
        $this->assertEquals('off',User::find(1)->flag);
        $this->assertCount(1,Flag::all());
        $this->assertEquals($startFlag,Flag::first()->started_at);
        $this->assertEquals($stopFlag,Flag::first()->stopped_at);
        $this->assertEquals(app('settings')->getFlags()['lost_time'],Flag::first()->seconds);
        $this->assertEquals(1,Flag::first()->user_id);
        $this->assertEquals(1,Flag::first()->work_time_id);
        $this->assertEquals($startFlag->toDateString(),Flag::first()->day);

        //make init state test request to check the final states
        $response = $this->json('GET', 'init_state');
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200, $response->status());
        $this->assertCount(5, $content);

        //test the flags field
        $flag = $content['flags'][0];
        $lostTime = app('settings')->getFlags()['lost_time'];
        $this->assertEquals($lostTime, $flag['limitSeconds']);
        $this->assertEquals($lostTime - Flag::first()->seconds, $flag['remainingSeconds']);
        $this->assertEquals(0, $flag['remainingSeconds']);
        $this->assertTrue(!$flag['inUse']);

        //test the work time signs field
        $workTimeSigns = $content['workTimeSigns'];
        $this->assertCount(2, $workTimeSigns);
        $this->assertEquals($startWork->toTimeString(),$workTimeSigns[0]['started_at']);
        $this->assertEquals($startWork->copy()->addHours(2)->addMinute(90)->toTimeString(),$workTimeSigns[0]['stopped_at']);
        $this->assertEquals('work',$workTimeSigns[0]['status']);
        $this->assertEquals($startWork->copy()->addHours(2)->addMinute(90)->toTimeString(),$workTimeSigns[1]['started_at']);
        $this->assertEquals(null,$workTimeSigns[1]['stopped_at']);
        $this->assertEquals('work',$workTimeSigns[1]['status']);

        //test the status field
        $this->assertEquals('on', $content['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $todaySeconds = $stopFlag->diffInSeconds($startWork) + app('settings')->getFlags()['lost_time'] - $stopFlag->diffInSeconds($startFlag);
        $this->assertEquals($todaySeconds, $todayTime['seconds']);
        $this->assertEquals('work', $todayTime['workStatus']);

        //test the month report
        $monthReport = $content['month_report'];
        $this->assertEquals($todaySeconds, $monthReport['actual']['seconds']);
        $this->assertEquals($monthReport['ideal']['seconds'] - $todaySeconds, $monthReport['diff']['seconds']);
        $this->assertEquals('less', $monthReport['diff']['type']);
    }

    /**
     * Test stop working using lost time flag and passing the flags
     * time limit
     */
    public function test_stop_work_while_using_time_flag_and_passing_the_time_limit()
    {
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        app('settings')->refreshData();
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        //set start work time
        $startWork = (new Carbon())->hour(12);
        Carbon::setTestNow($startWork);

        //make the test request to to start_work
        $response = $this->json('POST', 'start_work', ['workStatus' => 'work']);
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($startWork->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals(null,$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(0,$todayTime['seconds']);

        //set start lost time flag
        $startFlag = $startWork->copy()->addHours(2);
        Carbon::setTestNow($startFlag);

        //make the test request to start flag
        $response = $this->json('POST','/flag/start',['type' => 'lost_time']);
        $content = json_decode($response->content(),true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the returned message
        $this->assertEquals('user started using lost_time flag',$content['message']);

        //test the started flag
        $this->assertEquals('on',User::find(1)->flag);
        $this->assertCount(1,Flag::all());
        $this->assertEquals($startFlag,Flag::first()->started_at);
        $this->assertEquals(null,Flag::first()->stopped_at);
        $this->assertEquals(0,Flag::first()->seconds);
        $this->assertEquals(1,Flag::first()->user_id);
        $this->assertEquals(1,Flag::first()->work_time_id);
        $this->assertEquals($startFlag->toDateString(),Flag::first()->day);

        //set stop work time
        $stopWork = $startWork->copy()->addHours(4);
        Carbon::setTestNow($stopWork);

        //make the stop_work request
        $response = $this->json('POST','stop_work');
        $content = json_decode($response->content(),true);

        //test the response was succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($startWork->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals($stopWork->toTimeString(),$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(2.5 * 60 * 60,$todayTime['seconds']);


        //make init state test request to check the final states
        $response = $this->json('GET', 'init_state');
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200, $response->status());
        $this->assertCount(5, $content);

        //test the flags field
        $flag = $content['flags'][0];
        $lostTime = app('settings')->getFlags()['lost_time'];
        $this->assertEquals($lostTime, $flag['limitSeconds']);
        $this->assertEquals(0, $flag['remainingSeconds']);
        $this->assertTrue(!$flag['inUse']);

        //test the work time signs field
        $workTimeSigns = $content['workTimeSigns'];
        $this->assertCount(1, $workTimeSigns);
        $this->assertEquals($startWork->toTimeString(),$workTimeSigns[0]['started_at']);
        $this->assertEquals($stopWork->toTimeString(),$workTimeSigns[0]['stopped_at']);
        $this->assertEquals('work',$workTimeSigns[0]['status']);

        //test the status field
        $this->assertEquals('off', $content['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $todaySeconds = $startFlag->diffInSeconds($startWork) + app('settings')->getFlags()['lost_time'];
        $this->assertEquals($todaySeconds, $todayTime['seconds']);
        $this->assertEquals('work', $todayTime['workStatus']);

        //test the month report
        $monthReport = $content['month_report'];
        $this->assertEquals($todaySeconds, $monthReport['actual']['seconds']);
        $this->assertEquals($monthReport['ideal']['seconds'] - $todaySeconds, $monthReport['diff']['seconds']);
        $this->assertEquals('less', $monthReport['diff']['type']);
    }

    /**
     * Test start the work in a day and stop working in the
     * day after
     */
    public function test_start_work_in_day_stop_in_the_day_after()
    {
        //create initial settings and dummy testing users data
        Artisan::call('seed:settings');
        app('settings')->refreshData();
        Artisan::call('seed:users');

        //fake login
        $this->loginUser(1);

        //set start work time
        $startWork = (new Carbon())->hour(20)->minute(0)->second(0);
        Carbon::setTestNow($startWork);

        //make the test request to to start_work
        $response = $this->json('POST', 'start_work', ['workStatus' => 'work']);
        $content = json_decode($response->content(), true);

        //test that response succeeded
        $this->assertEquals(200,$response->status());
        $this->assertCount(2,$content);

        //test the work time sign field
        $workTimeSign = $content['workTimeSign'];
        $this->assertCount(3,$workTimeSign);
        $this->assertEquals($startWork->toTimeString(),$workTimeSign['started_at']);
        $this->assertEquals(null,$workTimeSign['stopped_at']);
        $this->assertEquals('work',$workTimeSign['status']);

        //test the today time field
        $todayTime = $content['today_time'];
        $this->assertCount(2,$todayTime);
        $this->assertEquals(0,$todayTime['seconds']);

        //set stop work time after 6 hours
        $stopWork = $startWork->copy()->addHours(6);
        Carbon::setTestNow($stopWork);

        //make the stop work request
        $response = $this->json('POST','/stop_work');
        $content = json_decode($response->content(),true);
    }
}
