<?php

namespace App\Console\Commands;

use App\Project;
use App\Task;
use App\User;
use App\WorkTime;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SeedDummyWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:dummy-work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds dummy work for temporary use';

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
        Schema::disableForeignKeyConstraints();
        WorkTime::query()->truncate();
        Task::query()->truncate();

        $dummyProject = Project::where('title', 'Not Listed')->first();

        $task = new Task();
        $task->content = 'Dummy Task';
        $task->project_id = $dummyProject->id;
        $task->save();

        $firstDayOfMonth = now()->firstOfMonth();
        $days = now()->diffInDays($firstDayOfMonth);

        $users = User::all();
        foreach ($users as $user) {
            $workTimes = [];
            for ($i = 0; $i <= $days; $i++) {
                $day = $firstDayOfMonth->copy()->addDays($i);
                $startedAt = $day->copy()->setTime(10,0);
                $stoppedAt = $day->copy()->setTime(18,0);
                if (! $day->isWeekend()) {
                    $workTimes[] = [
                        'user_id' => $user->id,
                        'task_id' => $task->id,
                        'project_id' => $dummyProject->id,
                        'day' => $day->toDateString(),
                        'started_work_at' => $startedAt,
                        'stopped_work_at' => $stoppedAt,
                        'seconds' => 8 * 60 * 60,
                        'day_seconds' => 8 * 60 * 60,
                    ];
                }
            }

            WorkTime::query()->insert($workTimes);
        }
    }
}
