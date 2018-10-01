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

Route::get('/flag/start','FlagsController@startFlag');
Route::get('/flag/end','FlagsController@endFlag');

Route::get('/status','SearchWorkStatusController@index')
    ->name('status.index');

Route::get('{any}', 'HomeController@index')
    ->name('home');


//Route::get('test', function () {
//    $client = new GuzzleHttp\Client;
//    $client->post('https://fcm.googleapis.com/fcm/send', [
//        'headers' => [
//            'Authorization' => 'key=AAAAb-k3kcE:APA91bFsapb-_ia8k8VCl7eJg9yQTgncZD_FVIQxNLsy5OHqX5BPxgCYMYp88ux0GGSkTXlge-V42vrZLIZ0CmAWBAS1CceRIRqvKZVT1d5h-V_rvz2jda9xS6esh8e403xANWeXMW2E',
//            'Content-Type' => 'application/json'
//        ],
//        'json' => [
//            'notification' => [
//                'title' => 'Sba7 el zeft',
//                'body' => 'Yala bena',
//            ],
//            'to' => 'fgdcY4C3bxM:APA91bFUu-nvdRdb1XO6YtFCtKGgmCUg4CD8YsLzxhqz0wj4u5X1KEcCoN5q5Iuia672LbkWdiMs9wZMJ9lNyg210Y8tJNLJZzIZMjRgBrZbQu7uzjcAaJO7F77YCO6d42TtB7zl5YHN'
//        ]
//    ]);
//});
