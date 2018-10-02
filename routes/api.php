<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::post('api/login','HybridAuthController@login')->name('login');
Route::post('api/logout','HybridAuthController@logout');

Route::post('start_work', 'SignController@startWork')->name('start_work');
Route::post('stop_work', 'SignController@stopWork')->name('stop_work');
Route::get('init_state', 'StatsController@init')->name('init_state');

Route::get('vacations/weekends', 'WeekendsController@index')->name('get_all_vacations');
Route::post('vacations/weekends', 'WeekendsController@update')->name('update_vacations');

Route::get('vacations/annual', 'AnnualVacationsController@index')->name('get_all_annual_vacations');
Route::post('vacations/annual/delete', 'AnnualVacationsController@delete')->name('delete_annual_vacation');
Route::post('vacations/annual/add', 'AnnualVacationsController@add')->name('add_annual_vacation');

Route::get('vacations/custom', 'CustomVacationsController@index')->name('get_all_custom_vacations');
Route::post('vacations/custom', 'CustomVacationsController@store')->name('store_custom_vacation');
Route::post('vacations/custom/delete', 'CustomVacationsController@delete')->name('delete_custom_vacation');

Route::post('browser/token','BrowserTokenController@store')->name('store_browser_token');
Route::get('users','UserSearchController@index');

Route::get('/regular/time','RegularTimeController@index')
    ->name('regular.time.index');
Route::post('/regular/time','RegularTimeController@store')
    ->name('regular.time.store');

Route::get('/status','SearchWorkStatusController@index')
    ->name('status.index');

Route::get('/flag/start','FlagsController@startFlag');
Route::get('/flag/end','FlagsController@endFlag');

Route::get('/status','SearchWorkStatusController@index')
    ->name('status.index');
