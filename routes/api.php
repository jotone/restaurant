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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::group(['middleware'=>'api', 'namespace'=> 'Api'], function(){
	//Registration
	Route::post('/create_account',		'RegisterController@createAccount');
	Route::put('/submit_sms_code/{id}',	'RegisterController@submitSmsCode');
	Route::put('/submit_profile/{id}',	'RegisterController@submitProfile');
});
