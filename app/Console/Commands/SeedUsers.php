<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Faker\Generator as Faker;

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
    public function handle(Faker $faker)
    {
        $names = [
            'Ibrahim','Ahmed','Ali','Mohamed','Said','Mona','Mahmud','Kareem','Nada','Shady'
        ];
        $mobile = '0122159011';
        $users = [];
        $users[] = [
            'id' => 1,
            'name' => 'DevOps',
            'username' => 'dev',
            'mobile' => '00000000',
            'email' => 'dev@ops.dev',
            'is_admin' => true,
            'password' => Hash::make('123456'),
            'tracked' => 0,
            'work_anywhere' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ];
        $count = 2;
        foreach ($names as $name) {
            $users[] = [
                'id' => $count,
                'name' => $name,
                'username' => strtolower($name),
                'mobile' => $mobile . $count,
                'email' => strtolower($name) . '@email.com',
                'is_admin' => false,
                'password' => Hash::make('123456'),
                'tracked' => rand(0,1),
                'work_anywhere' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $count++;
        }

        $users[] = [
            'id' => 12,
            'name' => 'ADMIN',
            'username' => 'admin',
            'mobile' => '000000000',
            'email' => 'admin@mail.com',
            'is_admin' => true,
            'work_anywhere' => true,
            'tracked' => false,
            'password' => Hash::make('123456'),
            'created_at' => now(),
            'updated_at' => now()
        ];
        Schema::disableForeignKeyConstraints();
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();
        DB::table('users')->insert($users);

    }
}
