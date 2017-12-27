<?php
Auth::routes();
Route::any('/logout', 'Auth\LoginController@logout')->name('logout');

//Route::get('/', 'Site\HomeController@index')->name('home');

/*Admin Block*/

Route::get('/admin/login', 'Admin\AuthController@loginPage')->name('admin.login.view');
Route::post('/admin/login', 'Admin\AuthController@login')->name('admin.login');

Route::group(['middleware' => 'admin', 'prefix'=>'/admin', 'as'=>'admin.', 'namespace'=>'Admin'], function() {
	Route::get('/', 'HomeController@index')->name('home');
	Route::get('/info', function(){
		return phpinfo();
	});


	//Gallery
	Route::get('/settings/gallery', 'GalleryController@index')->name('gallery.index');
		Route::get('/settings/gallery/all', 'GalleryController@all')->name('gallery.all');
		Route::post('/settings/gallery/create', 'GalleryController@create')->name('gallery.create');
		Route::delete('/settings/gallery/drop_unused', 'GalleryController@dropUnused')->name('gallery.dropUnused');
		Route::delete('/settings/gallery/{image}', 'GalleryController@destroy')->name('gallery.destroy');


	//Settings -> Main info
	Route::get('/settings/main_info', 'MainInfoController@index')->name('main_info.index');
		Route::put('/settings/main_info/{id?}', 'MainInfoController@update')->name('main_info.update');
	//Settings
	Route::get('/settings', 'SettingsController@index')->name('settings.index');
		Route::put('/settings/{id?}', 'SettingsController@update')->name('settings.update');


	//Templates
	Route::resource('/pages/templates', 'TemplatesController');
	//Pages
	Route::get('/pages', 'PagesController@index')->name('pages.index');
	Route::get('/pages/create/{template_id?}', 'PagesController@create')->name('pages.create');
	Route::get('/pages/{id}/edit', 'PagesController@edit')->name('pages.edit');
	Route::get('/pages/{id}', 'PagesController@show')->name('pages.show');
	Route::post('/pages', 'PagesController@store')->name('pages.store');
	Route::put('/pages/{id}', 'PagesController@update')->name('pages.update');
	Route::delete('/pages/{id}', 'PagesController@destroy')->name('pages.destroy');
		//Page Content
		Route::get('/page_content/{id}', 'PageContentController@getContentData');


	//Categories
	Route::resource('/category_types', 'CategoriesTypesController');
		Route::get('/category/create/{type}', 'CategoriesController@create')->name('category.create');
		Route::get('/category/{category}/edit', 'CategoriesController@edit')->name('category.edit');
		Route::post('/category', 'CategoriesController@store')->name('category.store');
		Route::put('/category/{category}', 'CategoriesController@update')->name('category.update');
		Route::patch('/category/{category}', 'CategoriesController@replace')->name('category.replace');
		Route::delete('/category/{category}', 'CategoriesController@destroy')->name('category.destroy');


	//Dishes
	Route::resource('/restaurant/menu/dish', 'MealDishController');
	Route::post('/restaurant/menu/dish/create_model_file', 'MealDishController@create_model_file');
	//Dish Menus
	Route::resource('/restaurant/menu', 'MealMenuController');
	//Restaurants
	Route::resource('/restaurant', 'RestaurantController');


	//Comments
	Route::resource('/comments', 'CommentsController', ['except' => ['create','store']]);

	//Roles
	Route::resource('/users/roles', 'RolesController');
	//Users
	Route::resource('/users', 'UsersController');
	//Visitors
	Route::resource('/visitors', 'VisitorsController');
});