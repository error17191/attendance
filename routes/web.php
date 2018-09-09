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

Route::get('/home', 'HomeController@index')->name('home');

Route::post('sign_in','SignController@signIn')->name('sign_in');
Route::post('sign_out','SignController@signOut')->name('sign_in');
Route::post('pause','SignController@pause')->name('pause');
Route::post('resume','SignController@resume')->name('resume');
