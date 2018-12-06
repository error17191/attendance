<?php

namespace App\Jobs;

use App\Events\FlagTimeExpired;
use App\User;
use App\Utilities\Flag;
use App\Utilities\WorKTime;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StopUserAfterFlagExpires implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->userId);

        if($user->isUsingFlag()){
            return;
        }

        Flag::stop(Flag::current($user->id))->save();
        WorKTime::stop(WorKTime::last($user->id))->save();
        broadcast(new FlagTimeExpired($user));
    }
}
