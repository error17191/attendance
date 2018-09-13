<?php

function partition_seconds($seconds)
{
    $hours = (int)floor($seconds / 60 / 60);
    $seconds -= $hours * 60 * 60;
    $minutes = (int)floor($seconds / 60);
    $seconds = (int)$seconds - $minutes * 60;

    return compact('hours', 'minutes', 'seconds');
}


function week_days()
{
    return [
        [
            'name' => 'Saturday',
            'short_name' => 'Sat',
            'index' => 6
        ],
        [
            'name' => 'Sunday',
            'short_name' => 'Sun',
            'index' => 0
        ],
        [
            'name' => 'Monday',
            'short_name' => 'Mon',
            'index' => 1
        ],
        [
            'name' => 'Tuseday',
            'short_name' => 'Tus',
            'index' => 2
        ],
        [
            'name' => 'Wednesday',
            'short_name' => 'Wed',
            'index' => 3
        ],
        [
            'name' => 'Thursday',
            'short_name' => 'Thu',
            'index' => 4
        ],
        [
            'name' => 'Friday',
            'short_name' => 'Fri',
            'index' => 5
        ],
    ];
}
