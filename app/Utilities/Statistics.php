<?php

namespace App\Utilities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class Statistics
{

    public static function monthReport(int $id,int $month,int $year = 0)
    {
        //TODO: better handling for month without work
        if(static::monthData($id,'work_times',$month,$year)->count() <= 0){
            return null;
        }
        $idealTime = static::monthIdeal($id,$month,$year);
        $actualTime = static::monthWork($id,$month,$year);
        $diffType = $actualTime < $idealTime ? 'less' : 'more';
        if($actualTime == $idealTime){
            $diffType = 'exact';
        }
        $diff = abs($actualTime - $idealTime);
        $status = static::monthWorkStatusTimes($id,$month,$year);
        $flags = static::monthFlags($id,$month,$year);
        $absence = static::monthAttendanceDays($id,$month,$year);
        $regularTime = static::monthRegularTime($id,$month,$year);
        $workEfficiency = static::monthWorkEfficiency($id,$month,$year);
        return compact('actualTime','idealTime','diffType','diff','status','flags','absence','regularTime','workEfficiency');
    }

    public static function dayReport(int $id,string $date)
    {
        $dayWorkTimes = static::dayData($id,'work_times',$date)->sortBy('started_work_at');
        if($dayWorkTimes->count() <= 0){
            $attended = false;
            $weekend = WorkDay::isWeekend(new Carbon($date));
            $vacation = WorkDay::isVacation($id,$date);
            return compact('attended','weekend','vacation');
        }
        $attended = true;
        $weekend = WorkDay::isWeekend(new Carbon($date));
        $vacation = WorkDay::isVacation($id,$date);
        $actualWork = $dayWorkTimes->sum('seconds');
        $timeAtWork = (new Carbon($dayWorkTimes->first()->started_work_at))->diffInSeconds($dayWorkTimes->last()->stopped_work_at);
        $workTimeLog = [];
        foreach ($dayWorkTimes as $dayWorkTime) {
            $workTimeLog[] = [
                'start' => (new Carbon($dayWorkTime->started_work_at))->toTimeString(),
                'stop' => (new Carbon($dayWorkTime->stopped_work_at))->toTimeString(),
                'duration' => $dayWorkTime->seconds
            ];
        }
        $flagsData = static::dayData($id,'flags',$date);
        $flags = [];
        $total = 0;
        foreach ($flagsData as $flagsDatum) {
            if(!isset($flags[$flagsDatum->type])){
                $flags[$flagsDatum->type] = 0;
            }
            $flags[$flagsDatum->type] += $flagsDatum->seconds;
            $total += $flagsDatum->seconds;
        }
        $flags['total'] = $total;
        $regularTimeFrom = app('settings')->getRegularTime()['from'] * 60 * 60;
        $regularTimeFrom = partition_seconds($regularTimeFrom);
        $regularTimeTo = app('settings')->getRegularTime()['to'] * 60 * 60;
        $regularTimeTo = partition_seconds($regularTimeTo);
        $regularTime = (new Carbon($dayWorkTimes->first()->started_work_at)) >= (new Carbon($dayWorkTimes->first()->day))->hour($regularTimeFrom['hours'])->minute($regularTimeFrom['minutes']) &&
            (new Carbon($dayWorkTimes->first()->started_work_at)) <= (new Carbon($dayWorkTimes->first()->day))->hour($regularTimeTo['hours'])->minute($regularTimeTo['minutes']);
        $regularHours = $actualWork >= app('settings')->getRegularTime()['regularHours'] * 60 * 60;
        $workEfficiency = round($actualWork / $timeAtWork * 100,2);
        return compact('attended','weekend','vacation','actualWork','timeAtWork','workTimeLog','flags','workEfficiency','regularTime','regularHours');
    }

    public static function yearReport(int $id,int $year):array
    {
        //TODO: handle months without work and year without work
        $workTime = static::yearWorkTime($id,$year);
        $flags = static::yearFlags($id,$year);
        $absence = static::yearAbsence($id,$year);
        $regularTime = static::yearRegularTime($id,$year);
        $workEfficiency = static::yearWorkEfficiency($id,$year);
        return compact('workTime','flags','absence','regularTime','workEfficiency');
    }

    public static function yearWorkTime(int $id,int $year):array
    {
        $yearWork = [];
        $total = [
            'name' => 'Total',
            'ideal' => 0,
            'actual' => 0,
            'diff' => 0
        ];
        $months = months();
        foreach ($months as $month) {
            $yearWork[$month['index']] =[
                'name' => $month['name'],
                'ideal' => static::monthIdeal($id,$month['index'],$year),
                'actual' => static::monthWork($id,$month['index'],$year)
            ];
            $yearWork[$month['index']]['diff'] = $yearWork[$month['index']]['actual'] - $yearWork[$month['index']]['ideal'];
            $yearWork[$month['index']]['diffType'] = $yearWork[$month['index']]['actual']['diff'] > 0 ? 'more' : 'less';
            if($yearWork[$month['index']]['diff'] === 0){
                $yearWork[$month['index']]['diffType'] = 'exact';
            }
            $total['ideal'] += $yearWork[$month['index']]['ideal'];
            $total['actual'] += $yearWork[$month['index']]['actual'];
            $total['diff'] += $yearWork[$month['index']]['diff'];
            $yearWork[$month['index']]['diff'] = abs($yearWork[$month['index']]['diff']);
        }
        $total['diffType'] = $total['diff'] > 0 ? 'more' : 'less';
        if($total['diff'] === 0){
            $total['diffType'] = 'exact';
        }
        $total['diff'] = abs($total['diff']);
        $yearWork[] = $total;
        return $yearWork;
    }

    public static function yearFlags(int $id,int $year):array
    {
        $yearFlags = [];
        $months = months();
        $total = ['name' => 'Total'];
        foreach ($months as $month) {
            $monthFlags = static::monthFlags($id,$month['index'],$year);
            $yearFlags[$month['index']] = $monthFlags;
            $yearFlags[$month['index']]['name'] = $month['name'];
            foreach ($monthFlags as $key => $value) {
                if(!isset($total[$key])){
                    $total[$key] = 0;
                }
                $total[$key] += $value;
            }
        }
        $yearFlags[] = $total;
        return $yearFlags;
    }

    public static function yearAbsence(int $id,int $year):array
    {
        $yearAbsence = [];
        $months = months();
        $total = [
            'name' => 'Total',
            'workDaysAbsence' => 0,
            'vacationsAttended' => 0
        ];
        foreach ($months as $month) {
            $yearAbsence[$month['index']] = static::monthAttendanceDays($id,$month['index'],$year);
            $yearAbsence[$month['index']]['name'] = $month['name'];
            $total['workDaysAbsence'] += count($yearAbsence[$month['index']]['workDaysAbsence']);
            $total['vacationsAttended'] += count($yearAbsence[$month['index']]['vacationsAttended']);
        }
        $yearAbsence[] = $total;
        return $yearAbsence;
    }

    public static function yearRegularTime(int $id,int $year):array
    {
        $yearRegularTime = [];
        $total = [
            'name' => 'Total',
            'all' => 0,
            'offTimes' => 0,
            'percentage' => 0
        ];
        $months = months();
        foreach ($months as $month) {
            $yearRegularTime[$month['index']] = static::monthRegularTime($id,$month['index'],$year);
            $yearRegularTime[$month['index']]['name'] = $month['name'];
            $total['all'] += $yearRegularTime[$month['index']]['all'];
            $total['offTimes'] += $yearRegularTime[$month['index']]['offTimes'];
        }
        $total['percentage'] = round(($total['all'] - $total['offTimes']) / $total['all'] * 100 ,2);
        $yearRegularTime[] = $total;
        return $yearRegularTime;
    }

    public static function yearWorkEfficiency(int $id,int $year)
    {
        $yearWorkEfficiency = [];
        $total = [
            'name' => 'Total',
            'attendedTime' => 0,
            'actualWork' => 0,
            'percentage' => 0
        ];
        $months = months();
        foreach ($months as $month) {
            $yearWorkEfficiency[$month['index']] = static::monthWorkEfficiency($id,$month['index'],$year);
            $yearWorkEfficiency[$month['index']]['name'] = $month['name'];
            $total['attendedTime'] += $yearWorkEfficiency[$month['index']]['attendedTime'];
            $total['actualWork'] += $yearWorkEfficiency[$month['index']]['actualWork'];
        }
        $total['percentage'] = round($total['actualWork'] / $total['attendedTime'] * 100 , 2);
        $yearWorkEfficiency[] = $total;
        return $yearWorkEfficiency;
    }

    public static function monthIdeal(int $id,int $month,int $year = 0):int
    {
        if(!$year){
            $year = now()->year;
        }
        $date = (new Carbon())->year($year)->month($month)->firstOfMonth();
        $regularHours = app('settings')->getRegularTime()['regularHours'];
        $monthLength = $date->copy()->diffInDays($date->copy()->lastOfMonth()) + 1;
        $ideal = 0;
        for($i = 0; $i < $monthLength; $i++){
            $day = (new Carbon())->year($year)->month($month)->firstOfMonth()->addDays($i);
            if(WorkDay::isNotAWorkDay($id,$day)){
                continue;
            }
            $ideal += $regularHours;
        }
        return $ideal * 60 * 60;
    }

    public static function monthWork(int $id,int $month,int $year = 0):int
    {
        return static::monthData($id,'work_times',$month,$year)->sum('seconds');
    }

    public static function monthWorkStatusTimes(int $id,int $month,int $year = 0):array
    {
        $status = [];
        $workTimes = static::monthData($id,'work_times',$month,$year);
        foreach ($workTimes as $workTime) {
            if(!isset($status[$workTime->status])){
                $status[$workTime->status] = 0;
            }
            $status[$workTime->status] += $workTime->seconds;
        }
        return $status;
    }

    public static function monthFlags(int $id,int $month,int $year = 0):array
    {
        $flags = [];
        $total = 0;
        $monthFlags = static::monthData($id,'flags',$month,$year);
        foreach ($monthFlags as $monthFlag) {
            if(!isset($flags[$monthFlag->type])){
                $flags[$monthFlag->type] = 0;
            }
            $flags[$monthFlag->type] += $monthFlag->seconds;
            $total += $monthFlag->seconds;
        }
        $flags['total'] = $total;
        return $flags;
    }

    public static function monthAttendanceDays(int $id,int $month,int $year = 0):array
    {
        $days = static::monthData($id,'work_times',$month,$year)->groupBy('day')->keys()->toArray();
        $vacationsAttended = [];
        $workDaysAbsence = [];
        $monthDays = static::monthDays($month);
        foreach ($monthDays as $monthDay) {
            if(WorkDay::isNotAWorkDay($id,(new Carbon($monthDay)))){
                if(in_array($monthDay,$days)){
                    $vacationsAttended[] = $monthDay;
                }
            }else{
                if(!in_array($monthDay,$days)){
                    $workDaysAbsence[] = $monthDay;
                }
            }
        }
        return compact('vacationsAttended','workDaysAbsence');
    }

    public static function monthRegularTime(int $id,int $month,int $year = 0):array
    {
        $regularTimeFrom = app('settings')->getRegularTime()['from'] * 60 * 60;
        $regularTimeFrom = partition_seconds($regularTimeFrom);
        $regularTimeTo = app('settings')->getRegularTime()['to'] * 60 * 60;
        $regularTimeTo = partition_seconds($regularTimeTo);
        $workDays = static::monthData($id,'work_times',$month,$year);
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

    public static function monthWorkEfficiency(int $id,int $month,int $year = 0):array
    {
        $workTimes = static::monthData($id,'work_times',$month,$year)->groupBy('day');
        $actualWork = 0;
        $attendedTime = 0;
        foreach ($workTimes as $workTime) {
            /** @var Collection $workTime */
            $workTime = $workTime->sortBy('started_work_at');
            $actualWork += $workTime->sum('seconds');
            $attendedTime += (new Carbon($workTime->last()->stopped_work_at))->diffInSeconds($workTime->first()->started_work_at);
        }
        $percentage = round($actualWork / $attendedTime * 100 , 2);
        return compact('attendedTime','actualWork','percentage');
    }

    public static function monthDays(int $month):array
    {
        $monthLength = (new Carbon())->month($month)->firstOfMonth()->diffInDays((new Carbon())->month($month)->lastOfMonth()) + 1;
        $days = [];
        for($i = 0; $i < $monthLength; $i++){
            $days[] = (new Carbon())->month($month)->firstOfMonth()->addDays($i)->toDateString();
        }
        return $days;
    }

    public static function dayData(int $id,string $table,string $date):Collection
    {
        return DB::table($table)->where('user_id',$id)
            ->where('day',$date)
            ->get();
    }

    public static function monthData(int $id,string $table,int $month,int $year = 0):Collection
    {
        if(!$year){
            $year = now()->year;
        }
        return DB::table($table)->where('user_id',$id)
            ->whereBetween('day',
                [
                    (new Carbon())->year($year)->month($month)->firstOfMonth()->toDateString(),
                    (new Carbon())->year($year)->month($month)->lastOfMonth()->toDateString()
                ])
            ->get();
    }

    public static function yearData(int $id,string $table,int $year):Collection
    {
        return DB::table($table)->where('user_id',$id)
            ->whereBetween('day',
                [
                    (new Carbon())->year($year)->month(1)->firstOfMonth()->toDateString(),
                    (new Carbon())->year($year)->month(12)->lastOfMonth()->toDateString()
                ])
            ->get();
    }

}