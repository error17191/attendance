<?php

namespace App\Console\Commands;

use App\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

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
        Schema::disableForeignKeyConstraints();
        Project::query()->truncate();
        Schema::enableForeignKeyConstraints();
        Project::query()->insert([
            [
                'title' => 'Travninja',
                'hidden' => 0
            ],
            [
                'title' => 'Attendance',
                'hidden' => 0
            ],
            [
                'title' => 'Offline Requests',
                'hidden' => 0
            ],
            [
                'title' => 'Syal Website',
                'hidden' => 0
            ],
            [
                'title' => 'Syal Tech Website',
                'hidden' => 0
            ],
            [
                'title' => 'Reporting System',
                'hidden' => 0
            ],
            [
                'title' => 'Practice',
                'hidden' => 0
            ],
            [
                'title' => 'Learning new stuff',
                'hidden' => 0
            ],
            [
                'title' => 'Dummy Project',
                'hidden' => 1
            ],
        ]);
    }
}
