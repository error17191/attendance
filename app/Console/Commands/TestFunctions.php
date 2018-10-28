<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Utilities\Statistics;

class TestFunctions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:function';

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
        dd(Statistics::dayReport(1,15,10,2018));
    }
}
