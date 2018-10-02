<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create dummy users data for testing';

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
        $names = [
            'Ibrahim','Ahmed','Ali','Mohamed','Said','Mona','Mahmud','Kareem','Nada','Shady'
        ];
        $mobile = '0122159011';
        $users = [];
        $count = 0;
        foreach ($names as $name) {
            $users[] = [
                'id' => $count + 1,
                'name' => $name,
                'username' => strtolower($name),
                'mobile' => $mobile . $count,
                'email' => strtolower($name) . '@email.com',
                'is_admin' => false,
                'machines' => '[]',
                'password' => Hash::make('123456'),
            ];
            $count++;
        }

        $users[] = [
            'id' => 11,
            'name' => 'ADMIN',
            'username' => 'admin',
            'mobile' => '000000000',
            'email' => 'admin@mail.com',
            'is_admin' => true,
            'machines' => '[]',
            'password' => Hash::make('123456')
        ];

        DB::table('users')->truncate();
        DB::table('users')->insert($users);

    }
}
