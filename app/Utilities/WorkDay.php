<?php

namespace App\Utilities;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkDay
{
    /**
     * @param int $id
     * @param \Carbon\Carbon $date
     * @return bool
     */
    public static function isNotAWorkDay(int $id,Carbon $date):bool
    {
        app('settings');
        return static::isWeekend($date) || static::isVacation($id,$date->toDateString());
    }

    /**
     * @param int $id
     * @param \Carbon\Carbon $date
     * @return bool
     */
    public static function isAWorkDay(int $id,Carbon $date):bool
    {
        return !static::isNotAWorkDay($id,$date);
    }

    /**
     * @param int $id
     * @param string $date
     * @return bool
     */
    public static function isVacation(int $id,string $date):bool
    {
        return static::isAnnualVacation($date) ||
            static::isGlobalCustomVacation($date) ||
            static::isUserCustomVacation($id,$date);
    }

    /**
     * @param string $date
     * @return bool
     */
    public static function isAnnualVacation(string $date):bool
    {
        return in_array($date,app('settings')->getAnnualVacations());
    }

    /**
     * @param string $date
     * @return bool
     */
    public static function isGlobalCustomVacation(string $date):bool
    {
        return DB::table('custom_vacations')->where('date',$date)->first() != null;
    }

    /**
     * @param int $id
     * @param string $date
     * @return bool
     */
    public static function isUserCustomVacation(int $id,string $date):bool
    {
        return DB::table('users')
                ->leftJoin('users_custom_vacations','users.id','users_custom_vacations.user_id')
                ->leftJoin('custom_vacations','users_custom_vacations.vacation_id','custom_vacations.id')
                ->select('custom_vacations.*')
                ->where('users.id',$id)
                ->where('custom_vacations.global',0)
                ->where('custom_vacations.date',$date)
                ->first() != null;
    }

    /**
     * @param \Carbon\Carbon $date
     * @return bool
     */
    public static function isWeekend(Carbon $date):bool
    {
        Carbon::setWeekStartsAt(0);
        return $date->isWeekend();
    }
}