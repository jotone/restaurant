<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\MealDish;
use App\MealMenu;

class DishesController extends ApiController
{
	/**
	 * @param $dishes
	 * @return array
	 */
	protected function createDishesList($dishes){
		$dish_list = [];
		foreach($dishes as $dish){
			//Asset square image
			$square_img = $dish->square_img;
			$square_img['src'] = (!empty($square_img['src']))? asset($square_img['src']): '';

			//Asset large image
			$large_img = $dish->large_img;
			$large_img['src'] = (!empty($large_img['src']))? asset($large_img['src']): '';

			$dish_list[] = [
				'id'			=> $dish->id,
				'title'			=> $dish->title,
				'category_id'	=> $dish->category_id,
				'square_img'	=> $square_img,
				'large_img'		=> $large_img,
				'model_3d'		=> $dish->model_3d,
				'price'			=> $dish->price,
				'dish_weight'	=> $dish->dish_weight,
				'calories'		=> $dish->calories,
				'text'			=> $dish->text,
				'cooking_time'	=> $dish->cooking_time,
				'is_recommended'=> $dish->is_recommended,
				'views'			=> $dish->views
			];
		}
		return $dish_list;
	}


	/**
	 * Take dish-data by ID
	 * GET|HEAD /api/get_dish/{id}
	 * @param $id \App\MealDish ID
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|string|\Symfony\Component\HttpFoundation\Response
	 */
	public function getByID($id){
		$dish = MealDish::find($id);

		if(empty($dish) || ($dish->enabled == 0)){
			return response(json_encode([
				'message' => 'Такого блюда не существует'
			]), 400);
		}

		$dish->views = $dish->views+1;
		$dish->save();

		$dish = $dish->toArray();

		//Asset square image
		$dish['square_img']['src'] = (!empty($dish['square_img']['src']))? asset($dish['square_img']['src']): '';

		//Asset large image
		$dish['large_img']['src'] = (!empty($dish['large_img']['src']))? asset($dish['large_img']['src']): '';
		//Unset unused array elements
		unset($dish['slug']);
		unset($dish['img_url']);
		unset($dish['enabled']);
		unset($dish['created_by']);
		unset($dish['updated_by']);
		unset($dish['created_at']);
		unset($dish['updated_at']);

		return json_encode($dish);
	}


	/**
	 * Take full list of dishes
	 * GET|HEAD /api/get_dishes
	 * @return json string
	 */
	public function getAll(){
		$dishes = MealDish::select(
			'id','title','category_id','square_img','large_img','model_3d',
			'price','dish_weight','calories','text','cooking_time','is_recommended','views'
		)->where('enabled','=',1)->get();

		$dish_list = $this->createDishesList($dishes);

		return json_encode($dish_list);
	}


	/**
	 * Take list of dishes by Restaurant ID
	 * GET|HEAD /api/get_dishes/{rest_id}
	 * @param $rest_id \App\Restaurant ID
	 * @return string
	 */
	public function getByRestaurant($rest_id){
		//Get menus for current restaurant
		$menus = MealMenu::select('dishes')
			->where('restaurant_id','=',$rest_id)
			->where('enabled','=',1)
			->get();

		//Create list of unique dish IDs
		$dishes_list = [];
		foreach($menus as $menu){
			$dishes = json_decode($menu->dishes);
			$dishes_list = array_merge($dishes_list, $dishes);
		}
		$dishes_list = array_values(array_unique($dishes_list));

		//Get dishes
		$dishes = MealDish::select('id','title','category_id','square_img','large_img','model_3d','price','dish_weight','calories','text','cooking_time','is_recommended','views')
			->where('enabled','=',1)
			->whereIn('id',$dishes_list)
			->get();

		$dish_list = $this->createDishesList($dishes);

		return json_encode($dish_list);
	}


	/**
	 * Take list of dishes by Restaurant ID and by Kitchen ID
	 * GET|HEAD /api/get_dishes/{rest_id}/kitchen/{kitch_id}
	 * @param $rest_id \App\Restaurant ID
	 * @param $kitch_id \App\Category ID
	 * @return json string
	 */
	public function getByKitchen($rest_id, $kitch_id){
		//Get menus for current restaurant
		$menus = MealMenu::select('dishes')
			->where('restaurant_id','=',$rest_id)
			->where('enabled','=',1)
			->get();

		//Create list of unique dish IDs
		$dishes_list = [];
		foreach($menus as $menu){
			$dishes = json_decode($menu->dishes);
			$dishes_list = array_merge($dishes_list, $dishes);
		}
		$dishes_list = array_values(array_unique($dishes_list));

		//Get dishes
		$dishes = MealDish::select('id','title','category_id','square_img','large_img','model_3d','price','dish_weight','calories','text','cooking_time','is_recommended','views')
			->where('enabled','=',1)
			->whereIn('id',$dishes_list)
			->where('category_id','LIKE','%"'.$kitch_id.'"%')
			->get();

		$dish_list = $this->createDishesList($dishes);

		return json_encode($dish_list);
	}
}