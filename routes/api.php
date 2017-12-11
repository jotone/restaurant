<?php
use Illuminate\Http\Request;

Route::group(['middleware'=>'api', 'namespace'=> 'Api'], function(){
	//Auth
	Route::post('/log_in', 'AuthController@login');
	//Registration
	Route::post('/create_account',		'RegisterController@createAccount');
	Route::put('/submit_sms_code/{id}',	'RegisterController@submitSmsCode');
	Route::put('/submit_profile/{id}',	'RegisterController@submitProfile');

	Route::get('/get_restaurants',		'RestaurantController@getAll');
});