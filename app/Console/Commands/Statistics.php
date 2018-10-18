<?php

namespace App\Console\Commands;

use App\Flag;
use App\User;
use Illuminate\Console\Command;
use Carbon\Carbon;
use App\WorkTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class Statistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        Carbon::setWeekStartsAt(0);
        Carbon::setWeekendDays([5,6]);
        $this->removeAttendance(1);
        $this->createAttendance(1,10);
        $actual = $this->getMonthWork(1,10);
        $ideal = $this->getMonthIdeal(1,10);
        $diffType = $actual < $ideal ? 'less' : 'more';
        $diff = abs($ideal - $actual);
        $status =  $this->getMonthWorkStatusTimes(1,10);
        $flags = $this->getMonthFlags(1,10);
        $daysReport = $this->getAttendanceDaysReport(1,10,$this->getAttendanceDays(1,10));
        $regularTome = $this->regularTimePercentage(1,10);
        //the report
        $this->info('Total work time for ' . User::find(1)->name . ' for October 2018 is ' . $actual . ' seconds');
        $this->info('He worked ' . partition_seconds($actual)['hours'] . ' hours');
        $this->info('The ideal work time for this month is ' . $ideal / 60 / 60 . ' hours');
        $this->info('He worked ' . $diffType . ' than the ideal work time by ' . $diff / 60 / 60 . ' hours');
        foreach ($status as $name => $value) {
            $this->info('He worked at ' .$name . ' for ' . partition_seconds($value)['hours'] . ' hours ' .
                partition_seconds($value)['minutes'] . ' minutes ' .
                partition_seconds($value)['seconds'] . ' seconds');
        }
        foreach ($flags as $name => $value) {
            $this->info('He used the ' .$name . ' flag for ' . partition_seconds($value)['hours'] . ' hours ' .
                partition_seconds($value)['minutes'] . ' minutes ' .
                partition_seconds($value)['seconds'] . ' seconds');
        }
        $this->info('He attended in ' . count($daysReport['vacations_attended']) . ' days of vacation days');
        $this->info('He absent in ' . count($daysReport['work_days_absence']) . ' days of work days');
        $this->info('He attended after regular time hours ' . $regularTome['offTimes'] . ' days');
        $this->info('The percentage of attending after the regular hours is ' . $regularTome['percentage']);
        return;
    }

    public function getMonthIdeal(int $id,int $month)
    {
        $day = (new Carbon())->month($month)->firstOfMonth();
        $regularHours = app('settings')->getRegularTime()['regularHours'];
        $monthLength = $day->diffInDays($day->copy()->lastOfMonth()) + 1;
        $ideal = 0;
        for($i = 0; $i < $monthLength; $i++){
            $day = (new Carbon())->month($month)->firstOfMonth()->addDays($i);
            if($day->isWeekend() || $this->isVacation($id,$day->toDateString())){
                continue;
            }
            $ideal += $regularHours;
        }
        return $ideal * 60 * 60;
    }

    public function getAttendanceDaysReport(int $id,int $month,$days)
    {
        $report = [];
        $report['vacations_attended'] = [];
        $report['work_days_absence'] = [];
        $monthDays = $this->getMonthDays($month);
        foreach ($monthDays as $monthDay) {
            if((new Carbon($monthDay))->isWeekend() || $this->isVacation($id,$monthDay)){
                if(in_array($monthDay,$days)){
                    $report['vacations_attended'][] = $monthDay;
                }
            }else{
                if(!in_array($monthDay,$days)){
                    $report['work_days_absence'][] = $monthDay;
                }
            }
        }
        return $report;
    }

    public function getMonthWork(int $id,int $month)
    {
        return $this->getMonthData($id,$month,'work_times')->sum('seconds');
    }

    public function getMonthWorkStatusTimes(int $id,int $month)
    {
        $status = [];
        $workTimes = $this->getMonthData($id,$month,'work_times');
        foreach ($workTimes as $workTime) {
            if(!isset($status[$workTime->status])){
                $status[$workTime->status] = 0;
            }
            $status[$workTime->status] += $workTime->seconds;
        }
        return $status;
    }

    public function getMonthFlags(int $id,int $month)
    {
        $flags = [];
        $monthFlags = $this->getMonthData($id,$month,'flags');
        foreach ($monthFlags as $monthFlag) {
            if(!isset($flags[$monthFlag->type])){
                $flags[$monthFlag->type] = 0;
            }
            $flags[$monthFlag->type] += $monthFlag->seconds;
        }
        return $flags;
    }

    public function getAttendanceDays(int $id,int $month)
    {
        return $this->getMonthData($id,$month,'work_times')->groupBy('day')->keys()->toArray();
    }

    public function getMonthData(int $id,int $month,string $table)
    {
        return DB::table($table)->where('user_id',$id)
            ->whereBetween('day',[now()->month($month)->firstOfMonth(),now()->month($month)->lastOfMonth()])
            ->get();
    }

    public function getMonthDays(int $month)
    {
        $monthLength = now()->month($month)->firstOfMonth()->diffInDays(now()->month($month)->lastOfMonth()) + 1;
        $days = [];
        for($i = 0; $i < $monthLength; $i++){
            $days[] = (new Carbon())->month($month)->firstOfMonth()->addDays($i)->toDateString();
        }
        return $days;
    }

    public function regularTimePercentage(int $id,int $month)
    {
        $regularTimeFrom = app('settings')->getRegularTime()['from'] * 60 * 60;
        $regularTimeFrom = partition_seconds($regularTimeFrom);
        $regularTimeTo = app('settings')->getRegularTime()['to'] * 60 * 60;
        $regularTimeTo = partition_seconds($regularTimeTo);
        $workDays = $this->getMonthData($id,$month,'work_times');
        /** @var Collection $workDays */
        $workDays = $workDays->groupBy('day');
        $all = $workDays->count();
        $offTimes = 0;
        $offDays = [];
        foreach ($workDays as $workDay) {
            $first = $workDay->sortBy('started_work_at')->first();
            if((new Carbon($first->started_work_at)) >= (new Carbon($first->day))->hour($regularTimeFrom['hours'])->minute($regularTimeFrom['minutes']) &&
                    (new Carbon($first->started_work_at)) <= (new Carbon($first->day))->hour($regularTimeTo['hours'])->minute($regularTimeTo['minutes'])){
                continue;
            }
            $offDays[] = $first->day;
            $offTimes++;
        }
        $percentage = round($offTimes / $all * 100 ,2);
        return compact('all','offDays','offTimes','percentage');
    }

    public function isVacation(int $id,string $date)
    {
        return $this->isGlobalVacation($date) || $this->isUserVacation($id,$date);
    }

    public function isGlobalVacation(string $date)
    {
        return DB::table('custom_vacations')->where('date',$date)->first() != null;
    }

    public function isUserVacation(int $id,string $date)
    {
        return DB::table('users')
                ->leftJoin('users_custom_vacations','users.id','users_custom_vacations.user_id')
                ->leftJoin('custom_vacations','users_custom_vacations.vacation_id','custom_vacations.id')
                ->select('custom_vacations.*')
                ->where('users.id',$id)
                ->where('custom_vacations.global',0)
                ->where('custom_vacations.date',$date)
                ->first() != null;
    }

    public function removeAttendance(int $id)
    {
        DB::table('work_times')->where('user_id',$id)->delete();
        DB::table('flags')->where('user_id',$id)->delete();
    }

    public function createAttendance(int $id,int $month)
    {
        $day = (new Carbon())->month($month)->firstOfMonth();
        $monthLength = $day->diffInDays($day->copy()->lastOfMonth()) + 1;
        $v = 0;
        for($i = 0; $i < $monthLength; $i++){
            $day = (new Carbon())->month($month)->firstOfMonth()->addDays($i);
            if($day->isWeekend() || $this->isVacation(1,$day->toDateString())){
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
        /** @var \App\User $user */
        $user = User::find($id);
        $workStatus = [
            'writing code','refactoring code','designing','testing'
        ];
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
            $workTime->status = $workStatus[rand(0,3)];
            $workTime->started_work_at = $day->copy()->hour($begin);
            $workTime->stopped_work_at = $day->copy()->hour($end);
            $workTime->seconds = $workTime->stopped_work_at->diffInSeconds($workTime->started_work_at);
            $workTime->day_seconds = $daySeconds + $workTime->seconds;
            $daySeconds = $workTime->day_seconds;
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
