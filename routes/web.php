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

Route::get('/notifications','NotificationController@index');
Route::get('/login', 'Auth\LoginController@showLoginForm');
Route::post('login','HybridAuthController@login')->name('login');
Route::post('logout','HybridAuthController@logout');

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
