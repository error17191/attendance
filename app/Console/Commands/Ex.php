<?php

namespace App\Console\Commands;

use App\Events\AdminChannel;
use App\Events\TestEvent;
use App\Notifications\WorkStart;
use App\User;
use Illuminate\Console\Command;

class Ex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ex';

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
        broadcast(new TestEvent());
    }
}
