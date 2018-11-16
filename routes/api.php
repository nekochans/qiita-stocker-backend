<?php


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

Route::middleware(['cors', 'xRequestId'])->group(function () {
    Route::get('weather', 'WeatherController@index');

    Route::options('accounts', function () {
        return response()->json();
    });

    Route::post('accounts', 'AccountController@create');

    Route::delete('accounts', 'AccountController@destroy');

    Route::options('login-sessions', function () {
        return response()->json();
    });

    Route::post('login-sessions', 'LoginSessionController@create');

    Route::options('categories', function () {
        return response()->json();
    });

    Route::get('categories', 'CategoryController@index');

    Route::post('categories', 'CategoryController@create');

    Route::patch('categories/{id}', 'CategoryController@update');
});
