<?php

namespace App\Console\Commands;

use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calc:month';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate Month Stats';

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
        $user = User::first();

        $today = today();
        $firstDay = now()->firstOfMonth();
        $daysCount = $today->diffInDays($firstDay);
        $shouldHaveSpent = 0;
        for ($i = 0; $i < $daysCount; $i++) {
            /** @var Carbon $day */
            $day = new Carbon($firstDay);
            $day->addDays($i);
            if($day->isWeekend()){
                $shouldHaveSpent += 8;
            }
        }

        $workTimes = $user->workTimes()->whereBetween('day',[$firstDay->toDateString(),Carbon::yesterday()->toDateString()])->get();
        $this->info($workTimes->count());

        $totalSecondsWorked = 0;

        foreach ($workTimes as $workTime){
            $totalSecondsWorked += $workTime->seconds;
        }
        $this->info($shouldHaveSpent);

        print_r(partition_seconds($totalSecondsWorked));

    }
}
