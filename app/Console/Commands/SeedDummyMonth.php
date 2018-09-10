<?php

namespace App\Console\Commands;

use App\Sign;
use App\User;
use App\WorkTime;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SeedDummyMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed Dummy Month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userId = User::first()->id;

        $today = today();
        $firstDay = now()->firstOfMonth();
        $daysCount = $today->diffInDays($firstDay);
        for ($i = 0; $i < $daysCount; $i++) {
            $day = new Carbon($firstDay);
            $day->addDays($i);
            $hours = rand(5,11);
            $minutes = rand(0,59);
            $seconds = rand(0,59);

            $totalSeconds = $seconds + 60 * $minutes + 60 * 60 * $hours;

            $workTime = new WorkTime();
            $workTime->seconds = $totalSeconds;
            $workTime->user_id = $userId;
            $workTime->day = $day->toDateString();
            $workTime->save();

            $sign =  new Sign();
            $sign->type = 'start' ;
            $day->addHours(8 + rand(0,10));
            $sign->day = $day->toDateString();
            $sign->time = $day->toTimeString();
            $sign->user_id = $userId;
            $sign->save();

            $sign = new Sign();
            $sign->type = 'stop';
            $day->addSeconds($totalSeconds);
            $sign->day = $day->toDateString();
            $sign->time = $day->toTimeString();
            $sign->user_id = $userId;
            $sign->save();
        }

    }
}
