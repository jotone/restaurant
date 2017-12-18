<?php
use Illuminate\Http\Request;

Route::group(['middleware'=>'api', 'namespace'=> 'Api'], function(){

	//Auth
	Route::post('/log_in',				'AuthController@login');

	//Registration
	Route::post('/create_account',		'RegisterController@createAccount');
	Route::put('/submit_sms_code/{id}',	'RegisterController@submitSmsCode');
	Route::put('/submit_profile/{id}',	'RegisterController@submitProfile');
	Route::put('/generate_sms/{id}',	'RegisterController@generateSMS');

	Route::get('/restore_password/{code}',	'RegisterController@restorePasswordGet');
	Route::post('/restore_password',		'RegisterController@restorePasswordSend');

	//Restaurants
	Route::get('/get_restaurants',		'RestaurantController@getAll');
	Route::get('/get_restaurant/{id}',	'RestaurantController@getOne');

	//Kitchen
	Route::get('/get_kitchen/{rest_id}/kitchen/{kitch_id}', 'KitchenController@getConcrete');
	Route::get('/get_kitchen/{rest_id}','KitchenController@getByRestaurant');
	Route::get('/get_kitchen',			'KitchenController@getAll');

	//Dishes
	Route::get('/get_dishes/{rest_id}/kitchen/{kitch_id}', 'DishesController@getByKitchen');
	Route::get('/get_dishes/{rest_id}',	'DishesController@getByRestaurant');
	Route::get('/get_dishes',			'DishesController@getAll');
	Route::get('/get_dish/{id}',		'DishesController@getByID');

	//Create Order
	Route::post('/create_order',		'VisitorsController@createOrder');
});