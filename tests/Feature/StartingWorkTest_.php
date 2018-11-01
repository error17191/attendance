<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\CommonActions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StartingWorkTest extends TestCase
{
    use RefreshDatabase, CommonActions;

    public function test_a_user_can_start_work()
    {
        $this->seedData();
        $this->logInUser(1);
        $time = '10:10:38';
        Carbon::setTestNow('2018-10-10 ' . $time);
        $response = $this->json('post', '/start_work', [
            'workStatus' => 'ABC'
        ]);
        $response->assertStatus(200);
        $content = $response->getContent();
        $data = json_decode($content, true);

        $this->assertArrayHasKey('workTimeSign', $data);
        $this->assertEquals($time, $data['workTimeSign']['started_at']);
    }

    public function test_a_user_cant_start_without_status()
    {
        $this->seedData();
        $this->logInUser(1);
        $response = $this->json('post', '/start_work');

        $response->assertStatus(422);
        $content = $response->getContent();
        $data = json_decode($content, true);

        $this->assertEquals('work_status_required', $data['status']);
    }

//    public function test_a_user_can_stop_work()
//    {
//        $this->seedData();
//        $this->logInUser(1);
//        $startTime = new Carbon('11:40:39');
//        Carbon::setTestNow($startTime);
//        $this->json('post', '/start_work');
//
//        $stopTime = new Carbon('12:39:11');
//        Carbon::setTestNow($stopTime);
//        $this->json('post', '/stop_work');
//    }
}
