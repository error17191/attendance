<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::post('start_work','SignController@startWork')->name('start_work');
Route::post('stop_work','SignController@stopWork')->name('stop_work');
Route::get('init_state','StatsController@init')->name('init_state');

Route::get('vacations/weekends','WeekendsController@index')->name('get_all_vacations');
Route::post('vacations/weekends','WeekendsController@update')->name('update_vacations');

Route::get('vacations/annual','AnnualVacationsController@index')->name('get_all_annual_vacations');
Route::post('vacations/annual/delete','AnnualVacationsController@delete')->name('delete_annual_vacation');
Route::post('vacations/annual/add','AnnualVacationsController@add')->name('add_annual_vacation');

Route::get('{any}/{path?}', 'HomeController@index')
    ->name('home')
    ->where('any', '(.*)')
    ->where('path', '(.*)');
