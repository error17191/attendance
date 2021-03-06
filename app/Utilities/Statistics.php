<?php

namespace App\Utilities;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use App\Project;
use App\Task;

class Statistics
{
    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function monthReport(int $id,int $month,int $year):array
    {
        if(static::monthHasNoWork($id,$month,$year)){
            return [
                'work_status' => false
            ];
        }
        $work_status = true;
        $idealTime = static::monthIdeal($id,$month,$year);
        $actualTime = static::monthWork($id,$month,$year);
        $diffType = $actualTime < $idealTime ? 'less' : 'more';
        if($actualTime == $idealTime){
            $diffType = 'exact';
        }
        $diff = abs($actualTime - $idealTime);
        $projectsWithTime = static::monthProjectTimes($id,$month,$year);
        $tasksWithTime = static::monthTasksTimes($id,$month,$year);
        $flags = static::monthFlags($id,$month,$year);
        $absence = static::monthAttendanceDays($id,$month,$year);
        $regularTime = static::monthRegularTime($id,$month,$year);
        $workEfficiency = static::monthWorkEfficiency($id,$month,$year);
        return compact('actualTime','idealTime','diffType','diff','tasksWithTime','flags','projectsWithTime','absence','regularTime','workEfficiency','work_status');
    }

    /**
     * @param int $id
     * @param string $date
     * @return array
     */
    public static function dayReport(int $id,string $date)
    {
        $dayWorkTimes = static::dayData($id,'work_times',$date)->sortBy('started_work_at');
        if($dayWorkTimes->count() <= 0){
            $work_status = false;
            $attended = false;
            $weekend = WorkDay::isWeekend(new Carbon($date));
            $vacation = WorkDay::isVacation($id,$date);
            return compact('attended','weekend','vacation','work_status');
        }
        $work_status = true;
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
        $projectsIds = $dayWorkTimes->pluck('project_id')->unique()->values();
        $projectsGroups = $dayWorkTimes->groupBy('project_id');
        $projects = Project::whereIn('id',$projectsIds)->get()->keyBy('id');
        $projectsWithTime = [];
        foreach ($projectsGroups as $projectId => $group) {
            $projectsWithTime[] = [
                'time' => $group->sum('seconds'),
                'project' => $projects->get($projectId)
            ];
        }
        $tasksIds = $dayWorkTimes->pluck('task_id')->unique()->values();
        $tasksGroups = $dayWorkTimes->groupBy('task_id');
        $tasks = Task::whereIn('id',$tasksIds)->get()->keyBy('id');
        $tasksWithTime = [];
        foreach ($tasksGroups as $taskId => $group) {
            $tasksWithTime[] = [
                'time' => $group->sum('seconds'),
                'task' => $tasks->get($taskId)
            ];
        }
        return compact('attended','tasksWithTime','projectsWithTime','weekend','vacation','actualWork','timeAtWork','workTimeLog','flags','workEfficiency','regularTime','regularHours','work_status');
    }

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
    public static function yearReport(int $id,int $year):array
    {
        //TODO: better handle for a month in year without work
        if(static::yearHasNoWork($id,$year)){
            return [
                'work_status' => false
            ];
        }
        $work_status = true;
        $workTime = static::yearWorkTime($id,$year);
        $projects = static::yearProjects($id,$year);
        $tasks = static::yearTasks($id,$year);
        $flags = static::yearFlags($id,$year);
        $absence = static::yearAbsence($id,$year);
        $regularTime = static::yearRegularTime($id,$year);
        $workEfficiency = static::yearWorkEfficiency($id,$year);
        return compact('workTime','projects','tasks','flags','absence','regularTime','workEfficiency','work_status');
    }

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
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
                'actual' => static::monthWork($id,$month['index'],$year),
                'work_status' => !static::monthHasNoWork($id,$month['index'],$year)
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

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
    public static function yearProjects(int $id,int $year):array
    {
        $yearProjects = [];
        $total = [
            'name' => 'total'
        ];
        $months = months();
        foreach ($months as $month) {
            $monthProjects = static::monthProjectTimes($id,$month['index'],$year);
            $yearProjects[$month['index']] = $monthProjects;
            $yearProjects[$month['index']]['name'] = $month['name'];
            $yearProjects[$month['index']]['work_status'] = !static::monthHasNoWork($id,$month['index'],$year);
            foreach ($monthProjects as $project) {
                if(!isset($total[$project['project']->title])){
                    $total[$project['project']->title] = 0;
                }
                $total[$project['project']->title] += $project['time'];
            }
        }
        $yearProjects[] = $total;
        return $yearProjects;
    }

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
    public static function yearTasks(int $id,int $year):array
    {
        $yearTasks = [];
        $total = [
            'name' => 'total'
        ];
        $months = months();
        foreach ($months as $month) {
            $monthTasks = static::monthTasksTimes($id,$month['index'],$year);
            $yearTasks[$month['index']] = $monthTasks;
            $yearTasks[$month['index']]['name'] = $month['name'];
            $yearTasks[$month['index']]['work_status'] = !static::monthHasNoWork($id,$month['index'],$year);
            foreach ($monthTasks as $task) {
                if(!isset($total[$task['task']->content])){
                    $total[$task['task']->content] = 0;
                }
                $total[$task['task']->content] += $task['time'];
            }
        }
        $yearTasks[] = $total;
        return $yearTasks;
    }

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
    public static function yearFlags(int $id,int $year):array
    {
        $yearFlags = [];
        $months = months();
        $total = ['name' => 'Total'];
        foreach ($months as $month) {
            $monthFlags = static::monthFlags($id,$month['index'],$year);
            $yearFlags[$month['index']] = $monthFlags;
            $yearFlags[$month['index']]['name'] = $month['name'];
            $yearFlags[$month['index']]['work_status'] = !static::monthHasNoWork($id,$month['index'],$year);
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

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
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
            $yearAbsence[$month['index']]['work_status'] = !static::monthHasNoWork($id,$month['index'],$year);
            $total['workDaysAbsence'] += count($yearAbsence[$month['index']]['workDaysAbsence']);
            $total['vacationsAttended'] += count($yearAbsence[$month['index']]['vacationsAttended']);
        }
        $yearAbsence[] = $total;
        return $yearAbsence;
    }

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
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
            $yearRegularTime[$month['index']]['work_status'] = !static::monthHasNoWork($id,$month['index'],$year);
            $total['all'] += $yearRegularTime[$month['index']]['all'];
            $total['offTimes'] += $yearRegularTime[$month['index']]['offTimes'];
        }
        $total['percentage'] = $total['all'] <= 0 ? 0 : round(($total['all'] - $total['offTimes']) / $total['all'] * 100 ,2);
        $yearRegularTime[] = $total;
        return $yearRegularTime;
    }

    /**
     * @param int $id
     * @param int $year
     * @return array
     */
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
            $yearWorkEfficiency[$month['index']]['work_status'] = !static::monthHasNoWork($id,$month['index'],$year);
            $total['attendedTime'] += $yearWorkEfficiency[$month['index']]['attendedTime'];
            $total['actualWork'] += $yearWorkEfficiency[$month['index']]['actualWork'];
        }
        $total['percentage'] = $total['attendedTime'] <= 0 ? 0 : round($total['actualWork'] / $total['attendedTime'] * 100 , 2);
        $yearWorkEfficiency[] = $total;
        return $yearWorkEfficiency;
    }

    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return int
     */
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

    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return int
     */
    public static function monthWork(int $id,int $month,int $year = 0):int
    {
        return static::monthData($id,'work_times',$month,$year)->sum('seconds');
    }

    public static function monthProjectTimes(int $id,int $month,int $year = 0):array
    {
        $workTimes = static::monthData($id,'work_times',$month,$year);
        $projectIds = $workTimes->pluck('project_id')->unique()->values();
        $groups = $workTimes->groupBy('project_id');
        $projects = Project::whereIn('id',$projectIds)->get()->keyBy('id');
        $projectsWithTime = [];
        foreach ($groups as $projectId => $group){
            $projectsWithTime[] = [
                'time' => $group->sum('seconds'),
                'project' => $projects->get($projectId)
            ];
        }
        return $projectsWithTime;
    }


    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function monthTasksTimes(int $id,int $month,int $year = 0):array
    {
        $workTimes = static::monthData($id,'work_times',$month,$year);
        $tasksIds = $workTimes->pluck('task_id')->unique()->values();
        $groups = $workTimes->groupBy('task_id');
        $tasks = Task::whereIn('id',$tasksIds)->get()->keyBy('id');
        $tasksWithTime = [];
        foreach ($groups as $taskId => $group){
            $tasksWithTime[] = [
                'time' => $group->sum('seconds'),
                'task' => $tasks->get($taskId)
            ];
        }
        return $tasksWithTime;
    }

    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return array
     */
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

    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return array
     */
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

    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return array
     */
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
        $percentage = $all <= 0 ? 0 : round($offTimes / $all * 100 ,2);
        return compact('all','offDays','offTimes','percentage');
    }

    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function monthWorkEfficiency(int $id,int $month,int $year = 0):array
    {
        $workTimes = static::monthData($id,'work_times',$month,$year)->groupBy('day');
        if($workTimes->count() <= 0){
            return [
                'attendedTime' => 0,
                'actualWork' => 0,
                'percentage' => 0
            ];
        }
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

    /**
     * @param int $month
     * @return array
     */
    public static function monthDays(int $month):array
    {
        $monthLength = (new Carbon())->month($month)->firstOfMonth()->diffInDays((new Carbon())->month($month)->lastOfMonth()) + 1;
        $days = [];
        for($i = 0; $i < $monthLength; $i++){
            $days[] = (new Carbon())->month($month)->firstOfMonth()->addDays($i)->toDateString();
        }
        return $days;
    }

    /**
     * @param int $id
     * @param string $table
     * @param string $date
     * @return Collection
     */
    public static function dayData(int $id,string $table,string $date):Collection
    {
        return DB::table($table)->where('user_id',$id)
            ->where('day',$date)
            ->get();
    }

    /**
     * @param int $id
     * @param string $table
     * @param int $month
     * @param int $year
     * @return Collection
     */
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

    /**
     * @param int $id
     * @param string $table
     * @param int $year
     * @return Collection
     */
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

    /**
     * @param string $day
     * @param array|null $ids
     * @return array
     */
    public static function daySummary(string $day,array $ids = null):array
    {
        $report = [];
        if(!$ids){
            $users = DB::table('users')->get();
        }else{
            $users = DB::table('users')->whereIn('id',$ids)->get();
        }
        foreach ($users as $user) {
            $report[$user->name] = static::dayReport($user->id,$day);
        }
        return $report;
    }

    /**
     * @param int $month
     * @param int $year
     * @param array|null $ids
     * @return array
     */
    public static function monthSummary(int $month,int $year,array $ids = null):array
    {
        $report = [];
        if(!$ids){
            $users = DB::table('users')->get();
        }else{
            $users = DB::table('users')->whereIn('id',$ids)->get();
        }
        foreach ($users as $user) {
            $report[$user->name] = static::monthReport($user->id,$month,$year);
        }
        return $report;
    }

    /**
     * @param int $year
     * @param array|null $ids
     * @return array
     */
    public static function yearSummary(int $year,array $ids = null):array
    {
        $report = [];
        if(!$ids){
            $users = DB::table('users')->get();
        }else{
            $users = DB::table('users')->whereIn('id',$ids)->get();
        }
        foreach ($users as $user) {
            $report[$user->name] = static::yearReport($user->id,$year);
        }
        return $report;
    }

    /**
     * @param array|null $projectsIds
     * @param array|null $usersIds
     * @return array
     */
    public static function projectUsersSummary(array $usersIds = null,array $projectsIds = null):array
    {
        $report = [];
        if(!$usersIds){
            $users = DB::table('users')->get();
        }else{
            $users = DB::table('users')->whereIn('id',$usersIds)->get();
        }
        if(!$projectsIds){
            $projects = Project::all();
        }else{
            $projects = Project::whereIn('id',$projectsIds)->get();
        }
        foreach ($projects as $project) {
            $report[$project->title] = [];
            $report[$project->title]['totalForSelectedUsers'] = 0;
            $usersProjectWorkTimes = $project->workTimes()->get();
            $report[$project->title]['total'] = $usersProjectWorkTimes->sum('seconds');
            $usersProjectWorkTimes = $usersProjectWorkTimes->groupBy('user_id');
            foreach ($usersProjectWorkTimes as $id => $group) {
                $user = $users->where('id',$id)->first();
                $seconds = $group->sum('seconds');
                if($user != null){
                   $report[$project->title][$user->name] = $seconds;
                   $report[$project->title]['totalForSelectedUsers'] += $seconds;
                }
            }
        }
        return $report;
    }

    /**
     * @param int $id
     * @param int $month
     * @param int $year
     * @return bool
     */
    public static function monthHasNoWork(int $id,int $month,int $year):bool
    {
        return static::monthData($id,'work_times',$month,$year)->count() <= 0;
    }

    /**
     * @param int $id
     * @param int $year
     * @return bool
     */
    public static function yearHasNoWork(int $id,int $year):bool
    {
        return DB::table('work_times')
            ->where('user_id',$id)
            ->whereBetween('day',[
                (new Carbon())->year($year)->startOfYear(),
                (new Carbon())->year($year)->lastOfYear()])
            ->get()->count() <= 0;
    }
}