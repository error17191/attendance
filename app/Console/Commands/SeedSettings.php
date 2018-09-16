<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds Settings';

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
        $settings = [
            [
                'key' => 'weekends',
                'value' => json_encode([])
            ],
            [
                'key' => 'annual_vacations',
                'value' => json_encode([])
            ],
        ];
        DB::table('settings')->truncate();
        DB::table('settings')->insert($settings);
    }
}
