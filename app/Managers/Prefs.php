<?php

namespace App\Managers;


use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Prefs
{
    protected $prefs;
    protected $weekends;

    public function __construct()
    {
        $this->prefs = json_decode(Storage::get('prefs/prefs.json'));
    }

    public function weekends($array = null)
    {
        if (is_null($array)) {
            return $this->prefs->weekends;
        }
        $this->prefs->weekends = $array;
        $this->save();
    }

    public function save()
    {
        Storage::put('prefs/prefs.json',json_encode($this->prefs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
