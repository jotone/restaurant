<?php
use Illuminate\Http\Request;

Route::group(['middleware'=>'api', 'namespace'=> 'Api'], function(){
	//Auth
	Route::post('/log_in',					'AuthController@login');

	//Registration
	Route::post('/create_account',			'RegisterController@createAccount');
	Route::put('/submit_sms_code/{id}',		'RegisterController@submitSmsCode');
	Route::put('/submit_profile/{id}',		'RegisterController@submitProfile');
	Route::put('/generate_sms/{id}',		'RegisterController@generateSMS');

	Route::put('/restore_password/{id}',		'RegisterController@restorePasswordSend');

	//Restaurants
	Route::get('/get_restaurants',			'RestaurantController@getAll');
	Route::get('/get_restaurants_by_kitchen/{kitch_id}','RestaurantController@getByKitchen');
	Route::get('/get_restaurant/{id}',		'RestaurantController@getOne');

	//Kitchen
	Route::get('/get_kitchen/{rest_id}/kitchen/{kitch_id}', 'KitchenController@getConcrete');
	Route::get('/get_kitchen/{rest_id}',	'KitchenController@getByRestaurant');
	Route::get('/get_kitchen',				'KitchenController@getAll');
	Route::get('/get_filter_kitchens',		'KitchenController@getFilterKitchens');

	//Dishes
	Route::get('/get_dishes/{rest_id}/kitchen/{kitch_id}', 'DishesController@getByKitchen');
	Route::get('/get_dishes/{rest_id}',		'DishesController@getByRestaurant');
	Route::get('/get_dishes',				'DishesController@getAll');
	Route::get('/get_dish/{id}',			'DishesController@getByID');

	//Create Order
	Route::post('/create_order',			'VisitorsController@createOrder');
	//Change User's Data
	Route::put('/change_data/{id}',			'VisitorsController@changeData');

	//Get page data
	Route::get('/get_page_data/{slug}',		'PageController@getPageData');
});