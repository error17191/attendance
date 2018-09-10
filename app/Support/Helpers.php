<?php

function partition_seconds($seconds)
{
    $hours = (int) floor($seconds / 60 / 60);
    $seconds -= $hours * 60 * 60;
    $minutes = (int) floor($seconds / 60);
    $seconds = (int) $seconds - $minutes * 60;

    return compact('hours', 'minutes', 'seconds');
}
