<?php

namespace App\Console\Commands;

use App\Project;
use App\Task;
use App\Utilities\WorkDay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\WorkTime;
use App\Flag;
use App\User;
use App\Utilities\Statistics;
use Faker\Factory;

class DummyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:attendance 
                                {id=1} 
                                {month=10} 
                                {year?}
                                {--all} 
                                {--remove}
                                {--remove_all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create testing attendance';

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
        $year = $this->argument('year') ?: now()->year;
        if($this->option('remove')){
            $this->removeAttendance($this->argument('id'));
            return;
        }
        if($this->option('remove_all')){
            $this->removeAll();
            return;
        }
        if($this->option('all')){
            for ($user = 1; $user <= 10; $user++) {
                $this->info($user);
                for($year = 2017; $year <= 2018; $year++){
                    $this->info($year);
                    for($month = 1; $month <= 12; $month++){
                        $this->info($month);
                        $this->createAttendance($user,$month,$year);
                    }
                }
            }
            return;
        }
        if($this->argument('year')){
            for($month = 1;$month <= 12; $month++){
                $this->info($month);
                $this->createAttendance($this->argument('id'),$month,$this->argument('year'));
            }
            return;
        }
        $this->createAttendance($this->argument('id'),$this->argument('month'),$year);
        return;
    }

    public function removeAll()
    {
        DB::table('work_times')->truncate();
        DB::table('flags')->truncate();
        DB::table('users')->orderBy('id')->each(function($user){
            $user->flag = 'off';
            $user->status = 'off';
        });
    }

    public function removeAttendance(int $id)
    {
        DB::table('work_times')->where('user_id',$id)->delete();
        DB::table('flags')->where('user_id',$id)->delete();
        DB::table('users')->where('id',$id)->update(['flag' => 'off','status' => 'off']);
    }

    public function createAttendance(int $id,int $month,int $year)
    {
        $day = (new Carbon())->day(1)->month($month)->year($year)->firstOfMonth();
        $endDay = (new Carbon())->day(1)->month($month)->year($year)->lastOfMonth();
        $length = $endDay->diffInDays($day);
        $v = 0;
        for($i = 0; $i < $length; $i++){
            $day = (new Carbon())->day(1 + $i)->month($month)->year($year);
            if(WorkDay::isNotAWorkDay($id,$day)){
                if(rand(0,1) && $v < 3){
                    $this->createDayAttendance($id,$day);
                    $v++;
                }
                continue;
            }
            if(rand(0,1) && $v < 3){
                $v++;
                continue;
            }
            $this->createDayAttendance($id,$day);
        }
    }

    public function createDayAttendance(int $id,Carbon $day)
    {
        $projects = Project::all();
        $start = 8;
        $stop = 23;
        $begin = rand($start,$start + 5);
        $end = rand($begin + 1,$begin + 5);
        $daySeconds = 0;
        while($end <= $stop || $daySeconds > (12 * 60 * 60)){
            $flags = app('settings')->getFlags();
            $flags = array_keys($flags);
            $workTime = new WorkTime();
            $workTime->user_id = $id;
            $workTime->day = $day->toDateString();
            $project = $projects->random();
            $workTime->project_id = $project->id;
            $workTime->task_id = $project->userTasks($id)->random()->id;
            $workTime->started_work_at = $day->copy()->hour($begin);
            $workTime->stopped_work_at = $day->copy()->hour($end);
            $workTime->seconds = $workTime->stopped_work_at->diffInSeconds($workTime->started_work_at);
            $daySeconds += $workTime->seconds;
            $workTime->save();
            $workTime->refresh();
            if(rand(0,1)){
                $flag = $flags[rand(0,count($flags) - 1)];
                $startFlag = $day->copy()->hour(rand($begin,$end));
                $limit = (new Carbon($workTime->stopped_work_at))->diffInSeconds($startFlag);
                if(gettype(app('settings')->getFlags()[$flag]) == 'integer'){
                    $secondsTillNow = DB::table('flags')->where('user_id',$id)
                        ->where('day',$day->toDateString())
                        ->where('type',$flag)
                        ->sum('seconds');
                    if($secondsTillNow < app('settings')->getFlags()[$flag]){
                        $remaining = app('settings')->getFlags()[$flag] - $secondsTillNow;
                        $limit = $limit >= $remaining ? $remaining : $limit;
                        if($limit >= 600){
                            $f = new Flag();
                            $f->user_id = $id;
                            $f->type = $flag;
                            $f->day = $day->toDateString();
                            $f->work_time_id = $workTime->id;
                            $f->started_at = $startFlag;
                            $f->seconds = rand(300,$limit);
                            $f->stopped_at = $f->started_at->copy()->addSeconds($f->seconds);
                            $f->save();
                        }
                    }
                }else{
                    if($limit >= 600){
                        $f = new Flag();
                        $f->user_id = $id;
                        $f->work_time_id = $workTime->id;
                        $f->type = $flag;
                        $f->day = $day->toDateString();
                        $f->started_at = $startFlag;
                        $f->seconds = rand(300,$limit);
                        $f->stopped_at = $f->started_at->copy()->addSeconds($f->seconds);
                        $f->save();
                    }
                }
            }
            $begin = rand($end + 1,$end + 2);
            $end = rand($begin + 1,$begin + 5);
        }
    }
}
