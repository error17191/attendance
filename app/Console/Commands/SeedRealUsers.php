<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SeedRealUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:real-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds real users for beta version';

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
        DB::table('users')->truncate();
        Schema::enableForeignKeyConstraints();
        DB::table('users')->insert([
            [
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
            ],
            [
                'id' => 2,
                'name' => 'Mohamed Ahmed',
                'username' => 'm.ahmed',
                'mobile' => '01220179432',
                'email' => 'error17191@gmail.com',
                'is_admin' => false,
                'work_anywhere' => true,
                'tracked' => false,
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Mohamed Ibrahim',
                'username' => 'm.ibrahim',
                'mobile' => '01015287494',
                'email' => 'mhmdibrahimabdellatif@gmail.com',
                'is_admin' => false,
                'work_anywhere' => true,
                'tracked' => false,
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Mahmoud Ahmed',
                'username' => 'mahmoud',
                'mobile' => '01278734721',
                'email' => 'sswsy2006@gmail.com',
                'is_admin' => false,
                'work_anywhere' => true,
                'tracked' => false,
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'Ibrahim Ahmed',
                'username' => 'ibrahim',
                'mobile' => '01221590118',
                'email' => 'ibrahim21383@gmail.com',
                'is_admin' => false,
                'work_anywhere' => true,
                'tracked' => false,
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => 'Karim Abd Elmegeed',
                'username' => 'karim',
                'mobile' => '01097378257',
                'email' => 'karim.gido@gmail.com',
                'is_admin' => false,
                'work_anywhere' => true,
                'tracked' => false,
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'name' => 'Basant Gamal',
                'username' => 'basant',
                'mobile' => '01097470653',
                'email' => 'basantgamal71@gmail.com',
                'is_admin' => false,
                'work_anywhere' => true,
                'tracked' => false,
                'password' => Hash::make('123456'),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
