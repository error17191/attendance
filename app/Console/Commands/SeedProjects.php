<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;

class SeedProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:projects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds Projects';

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
        Project::query()->insert([
            [
                'title' => 'Travninja'
            ],
            [
                'title' => 'Attendance'
            ],
            [
                'title' => 'Offline Requests'
            ],
            [
                'title' => 'Syal Website'
            ],
            [
                'title' => 'Syal Tech Website'
            ],
        ]);
    }
}
