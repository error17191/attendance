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

Route::get('vacations','VacationController@index')->name('get_all_vacations');
Route::post('vacations','VacationController@update')->name('update_vacations');

Route::get('{any}/{path?}', 'HomeController@index')
    ->name('home')
    ->where('any', '(.*)')
    ->where('path', '(.*)');
