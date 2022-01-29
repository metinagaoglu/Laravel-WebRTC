<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware([\Illuminate\Auth\Middleware\Authenticate::class])->group(function () {

    Route::get('/home', 'App\Http\Controllers\HomeController@index')->name('home');

    Route::get('/streaming', 'App\Http\Controllers\WebRTCStreamingController@index');
    Route::get('/streaming/{streamId}', 'App\Http\Controllers\WebRTCStreamingController@consumer');
    Route::post('/stream-offer', 'App\Http\Controllers\WebRTCStreamingController@makeStreamOffer');
    Route::post('/stream-answer', 'App\Http\Controllers\WebRTCStreamingController@makeStreamAnswer');
});
