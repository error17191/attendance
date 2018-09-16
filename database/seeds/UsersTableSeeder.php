<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\User::create([
            'name' => 'Some User',
            'email' => 'user@mail.com',
            'password' => bcrypt('123456')
        ]);

        factory(\App\User::class,20)->create();
    }
}
