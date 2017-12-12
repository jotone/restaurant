<?php
use Illuminate\Http\Request;

Route::group(['middleware'=>'api', 'namespace'=> 'Api'], function(){

	//Auth
	Route::post('/log_in', 'AuthController@login');

	//Registration
	Route::post('/create_account',		'RegisterController@createAccount');
	Route::put('/submit_sms_code/{id}',	'RegisterController@submitSmsCode');
	Route::put('/submit_profile/{id}',	'RegisterController@submitProfile');

	//Restaurants
	Route::get('/get_restaurants',		'RestaurantController@getAll');
	Route::get('/get_restaurant/{id}',	'RestaurantController@getOne');

	//Kitchen
	Route::get('/get_kitchen/{rest_id}/kitchen/{kitch_id}', 'KitchenController@getConcrete');
	Route::get('/get_kitchen/{rest_id}',	'KitchenController@getByRestaurant');
	Route::get('/get_kitchen',				'KitchenController@getAll');
});