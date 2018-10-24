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
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Attendance',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Offline Requests',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Syal Website',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Syal Tech Website',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Reporting System',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Practice',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Learning new stuff',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Not Listed',
                'hidden' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
