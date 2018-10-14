<?php

use App\UserSetting;
use Faker\Generator as Faker;

$factory->define(UserSetting::class, function (Faker  $faker) {
    return [
        'user_id'=>$faker->numberBetween(1,10),
        'work_anywhere'=>$faker->numberBetween(0,1),
        'tracked'=>$faker->numberBetween(0,1),

    ];
});
