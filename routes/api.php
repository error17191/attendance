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

Route::post('update_password','ChangePasswordController@update');
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

Route::get('regular/time','RegularTimeController@index')
    ->name('regular.time.index');
Route::post('regular/time','RegularTimeController@store')
    ->name('regular.time.store');

Route::get('status','SearchWorkStatusController@index')
    ->name('status.index');

Route::post('flag/start','FlagsController@startFlag');
Route::post('flag/end','FlagsController@endFlag');

Route::get('status','SearchWorkStatusController@index')
    ->name('status.index');

Route::post('machine/request','UserMachineController@requestWorkMachine');
Route::post('machine/accept','UserMachineController@acceptWorkMachine');
Route::post('machine/reject','UserMachineController@rejectWorkMachine');
Route::post('machine/delete','UserMachineController@deleteWorkMachine');
Route::post('machine/check','UserMachineController@checkUserMachine');
Route::post('machine/add','UserMachineController@addNewUserMachine');


Route::get('admin/flags','AdminFlagsController@index');
Route::post('admin/flag','AdminFlagsController@store');
Route::delete('admin/flags','AdminFlagsController@destroy');

Route::get('check/user/tracked/{user_id}','UserSettingsController@checkIfUserCanBeTracked');
Route::get('check/user/work/{user_id}','UserSettingsController@checkIfUserCanWorkAnywhere');

Route::get('month/report/admin','StatisticsController@monthReportAdmin');
Route::get('day/report/admin','StatisticsController@dayReportAdmin');

Route::post('me', 'HybridAuthController@me');


Route::get('user/info/{user_id}',function($user_id){
   $user=\App\User::find($user_id);
   return response()->json(['user'=>$user]);
});
