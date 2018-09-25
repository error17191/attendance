<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class IbrahimTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ibrahim:tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs tests made by ibrahim';

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
        $customVacations = DB::table('users')
            ->leftJoin('users_custom_vacations','users.id','users_custom_vacations.user_id')
            ->leftJoin('custom_vacations','users_custom_vacations.vacation_id','custom_vacations.id')
            ->select('custom_vacations.*')
            ->whereIn('users.id',[2])
            ->where('custom_vacations.global',0)
            ->orderBy('custom_vacations.date')
            ->get();
           dd($customVacations);
//        dd(DB::table('custom_vacations')->whereIn('id',[1,2,3])->get());
    }
}
