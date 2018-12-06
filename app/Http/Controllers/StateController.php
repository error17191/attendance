<?php

namespace App\Http\Controllers;

use App\Project;
use App\Task;
use App\Utilities\WorkDay;
use App\WorkTime;
use Carbon\Carbon;
use App\Utilities\WorKTime as UW;
use App\Utilities\Flag;
use Illuminate\Http\JsonResponse;

class StateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(): JsonResponse
    {
        $user = auth()->user();
        $id = $user->id;
        if (UW::startedToday($id)) {
            $todayWorkSeconds = UW::secondsTillNow($id, now()->toDateString());
            $todayWorkTimePartitions = partition_seconds($todayWorkSeconds);
        } else {
            $todayWorkSeconds = 0;
            $todayWorkTimePartitions = ['hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }
        $firstOfMonth = today()->firstOfMonth();
        $daysPassed = today()->diffInDays($firstOfMonth);// Days passed of current month
        $workHoursIdeal = 0;
        for ($i = 0; $i <= $daysPassed; $i++) {
            $day = (new Carbon($firstOfMonth))->addDays($i);
            if (WorkDay::isAWorkDay($id, $day)) {
                $workHoursIdeal += app('settings')->getRegularTime()['regularHours'];
            }
        }
        $workSecondsIdeal = 60 * 60 * $workHoursIdeal;
        $workSecondsActual = WorkTime::where('user_id', auth()->id())->whereBetween('day', [today()->firstOfMonth(), today()->subDay()])->sum('seconds');
        $workSecondsActual += $todayWorkSeconds;
        if ($workSecondsActual > $workSecondsIdeal) {
            $diffType = 'more';
        } else {
            $diffType = 'less';
        }
        $diffSeconds = abs($workSecondsIdeal - $workSecondsActual);
        $lastWorkTime = UW::last($id);
        return response()->json([
            'projects' => Project::visible()->get(),
            'flags' => Flag::today($id),
//            'workTimeSigns' => UW::todaySigns($id),
            'working' => $user->isWorking(),
            'today_time' => [
                'seconds' => $todayWorkSeconds,
                'partitions' => $todayWorkTimePartitions,
                'task' => optional($lastWorkTime)->task,
                'project' => optional($lastWorkTime)->project,
                //TODO: Make it conditional from request
                'project_tasks' => optional($lastWorkTime)->project ? Task::where('user_id', auth()->id())->where('project_id', $lastWorkTime->project->id)->get() : [],
            ],
            'month_report' => [
                'actual' => [
                    'seconds' => $workSecondsActual,
                    'partitions' => partition_seconds($workSecondsActual)
                ],
                'ideal' => [
                    'seconds' => $workSecondsIdeal,
                    'partitions' => partition_seconds($workSecondsIdeal)
                ],
                'diff' => [
                    'type' => $diffType,
                    'seconds' => $diffSeconds,
                    'partitions' => partition_seconds($diffSeconds)
                ]
            ]
        ]);
    }
}
