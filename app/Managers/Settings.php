<?php

namespace App\Managers;

use Illuminate\Support\Facades\DB;

class Settings
{
    public $settings;

    public function __construct()
    {
        $settings = DB::table('settings')->get();
        $this->settings = [];
        foreach ($settings as $setting) {
            $this->settings[$setting->key] = json_decode($setting->value, true);
        }
    }

    public function setWeekends($array = null)
    {
        $this->settings['weekends'] = $array;
        DB::table('settings')
            ->where('key', 'weekends')
            ->update(['value' => json_encodei($this->settings['weekends'])]);
    }

    public function getWeekends()
    {
        return $this->settings['weekends'];
    }

    public function getAnnualVacations()
    {
        return array_values($this->settings['annual_vacations']);
    }

    public function annualVacationExists($annualVacation)
    {
        $key = $annualVacation['month'] . '_' . $annualVacation['day'];
        return isset($this->settings['annual_vacations'][$key]);
    }

    public function addAnnualVacation($annualVacation)
    {
        $key = $annualVacation['month'] . '_' . $annualVacation['day'];
        $this->settings['annual_vacations'][$key] = $annualVacation;

        DB::table('settings')
            ->where('key', 'annual_vacations')
            ->update(['value' => json_encodei($this->settings['annual_vacations'])]);
    }

    public function removeAnnualVacation($annualVacation)
    {
        $key = $annualVacation['month'] . '_' . $annualVacation['day'];
        unset($this->settings['annual_vacations'][$key]);

        DB::table('settings')
            ->where('key', 'annual_vacations')
            ->update(['value' => json_encodei($this->settings['annual_vacations'])]);
    }
}
