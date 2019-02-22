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

Route::post('audio/transcribe', 'TranscriptionController@convert')->name('upload.audio');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/artisan', function () {
   //gets the artisan command from query string passed
 $data = Request::get('data');
   //executes the artisan command
 return shell_exec('php ../artisan '.$data);
});
