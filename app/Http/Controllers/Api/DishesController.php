<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\MealDish;
use App\MealMenu;

class DishesController extends ApiController
{
	/**
	 * GET|HEAD /api/get_dishes/{request?}
	 * @param null|base_64(json(obj)) $request
	 * [
	 * 		restaurant_id - \App\Restaurant ID (0 - all)
	 * 		kitchen_id - \App\Category ID (0 - all),
	 * 		quant - max quantity of dishes (0 - all)
	 * ]
	 * @return json string
	 */
	public function getDishes($request = null){
		$request = (!empty($request))? json_decode(base64_decode($request)): null;

		//Get menus list
		$menus = MealMenu::select('dishes')->where('enabled','=',1);
		//Get menus for current restaurant
		if(isset($request->restaurant_id) && ($request->restaurant_id > 0)){
			$menus = $menus->where('restaurant_id','=',$request->restaurant_id);
		}
		$menus = $menus->get();

		//Create list of unique dish IDs
		$dishes_list = [];
		foreach($menus as $menu){
			$dishes = json_decode($menu->dishes);
			$dishes_list = array_merge($dishes_list, $dishes);
		}
		$dishes_list = array_values(array_unique($dishes_list));

		//Get dishes
		$dishes = MealDish::select('id','title','category_id','large_img','model_3d',
			'price','dish_weight','calories','cooking_time','is_recommended')
			->where('enabled','=',1)
			->whereIn('id',$dishes_list);
		//Get dishes for current kitchen type
		if(isset($request->kitchen_id) && ($request->kitchen_id > 0)){
			$dishes = $dishes->where('category_id','LIKE','%"'.$request->kitchen_id.'"%');
		}
		//Set order
		$dishes = $dishes->orderBy('title','asc');
		//Set quantity
		if(isset($request->quant) && ($request->quant > 0)){
			$dishes = $dishes->limit($request->quant);
		}
		$dishes = $dishes->get();

		$dishes_list = [];
		foreach($dishes as $dish){
			$large_img = $dish->large_img;
			$large_img = (!empty($large_img['src']))? asset($large_img['src']): '';
			$dishes_list[] = [
				'title'			=> $dish->title,
				'kitchen'		=> $dish->category_id[0],
				'large_img'		=> $large_img,
				'model_3d'		=> $dish->model_3d,
				'price'			=> $dish->price,
				'dish_weight'	=> $dish->dish_weight,
				'calories'		=> $dish->calories,
				'cooking_time'	=> $dish->cooking_time,
				'is_recommended'=> $dish->is_recommended
			];
		}

		return json_encode($dishes_list);
	}
}