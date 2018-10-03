<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Artisan;
use App\User;

class AttendanceTest extends TestCase
{
   use RefreshDatabase;

   public function test_init_state_with_fresh_user()
   {
       Artisan::call('seed:settings');
       Artisan::call('seed:users');
       $this->assertEquals(11,User::all()->count());
       config(['app.enable_fake_login' => true]);
       config(['app.fake_logged_user' => 11]);
       $response = $this->json('GET','init_state');
       $content = json_decode($response->content(), true);
       dd($response->status());
   }
}
