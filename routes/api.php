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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('messenger')->group(function() {
    Route::get('/webhook', 'Api\MessengerController@challenge');
    Route::post('/webhook', 'Api\MessengerController@webhook')
        ->middleware('messenger.webhook');
    Route::get('/test', function() {
        phpinfo();
    });
});
