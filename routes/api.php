<?php
use Illuminate\Http\Request;

Route::group(['middleware'=>'api', 'namespace'=> 'Api'], function(){
	//Auth
	Route::post('/log_in',
				'AuthController@login');

	//Registration
	Route::post('/create_account',
				'RegisterController@createAccount');
	Route::put(	'/submit_sms_code/{id}',
				'RegisterController@submitSmsCode');
	Route::put(	'/submit_profile/{id}',
				'RegisterController@submitProfile');
	Route::put(	'/generate_sms/{id}',
				'RegisterController@generateSMS');
	Route::put(	'/restore_password/{id}',
				'RegisterController@restorePasswordSend');

	//Restaurants
	Route::get(	'/get_restaurants/{quant?}',
				'RestaurantController@getAllRestaurants');
	Route::get(	'/get_restaurant/{id}/{quant?}',
				'RestaurantController@getRestaurant');
	Route::get(	'/get_restaurants_by_filter/{request?}',
				'RestaurantController@getRestaurantsByFilter');

	//Kitchen
	Route::get(	'/get_kitchens',
				'KitchenController@getKitchens');

	//Dishes
	Route::get(	'/get_dishes/{request?}',
				'DishesController@getDishes');

	//Create Order
	Route::post('/create_order',
				'VisitorsController@createOrder');
	//Change User's Data
	Route::put(	'/change_data/{id}',
				'VisitorsController@changeData');
	//Get visits data
	Route::get(	'/get_visits/{user_id}',
				'VisitorsController@getAll');
	Route::get(	'/get_visit/{date}/restaurant/{rest_id}/user/{user_id}',
				'VisitorsController@getByDate');
	//Create comment
	Route::post('/add_comment',
				'VisitorsController@createComment');


	//Get page data
	Route::get(	'/get_page_data/{slug}',
				'PageController@getPageData');
});