<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class SeedCustomVacations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:custom_vacations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create dummy data for custom vacations';

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
        $customVacations = [];
        for($i=0; $i<10; $i++){
            $customVacations[] = [
                'global' => 1,
                'date' => (new Carbon())->month(rand(1,12))->day(rand(1,28))->toDateString()
            ];
        }
        Schema::disableForeignKeyConstraints();
        DB::table('custom_vacations')->truncate();
        Schema::disableForeignKeyConstraints();
        DB::table('custom_vacations')->insert($customVacations);
    }
}
