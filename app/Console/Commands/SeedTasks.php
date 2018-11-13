<?php

namespace App\Console\Commands;

use App\Project;
use App\Task;
use App\User;
use Illuminate\Console\Command;

class SeedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:tasks';

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
        $users = User::all();
        $projects = Project::all();
        foreach ($users as $user) {
            foreach ($projects as $project) {
                $length = rand(1,6);
                for($i=0; $i<$length; $i++){
                    $task = new Task();
                    $task->content =  'project ' . $project->title .  ' task ' . ($i + 1);
                    $task->user_id = $user->id;
                    $task->project_id = $project->id;
                    $task->save();
                }
            }
        }
    }
}
