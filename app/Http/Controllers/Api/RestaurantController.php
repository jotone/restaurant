<?php
namespace App\Http\Controllers\Api;

use App\Category;
use App\MealDish;
use App\MealMenu;
use App\Restaurant;
use App\VisitorsRates;

use App\Http\Controllers\ApiController;

class RestaurantController extends ApiController
{
	/**
	 * Function returns dish list by restaurant ID
	 * @param $restaurant_id \App\Restaurant ID
	 * @param null|integer $quant - quantity of viewed dishes
	 * @return array
	 */
	protected function getDishes($restaurant_id, $quant = null, $more_data = false){
		//Get menus by restaurant ID
		$menus = MealMenu::select('dishes')
			->where('restaurant_id','=',$restaurant_id)
			->where('enabled','=',1)
			->get();
		//Create list of dishes IDs for current restaurant
		$dishes_list = [];
		foreach($menus as $menu){
			$dishes = json_decode($menu->dishes);
			$dishes_list = array_merge($dishes_list, $dishes);
		}
		$dishes_list = array_values(array_unique($dishes_list));
		//Get count of restaurant dishes
		$dish_count = count($dishes_list);

		//Get each dish data
		//If no need etc data for dish
		$dishes = ($more_data == false)
			? MealDish::select('id','title','price')
			: MealDish::select('id','title','price','calories','cooking_time','dish_weight');

		$dishes = $dishes->where('enabled','=',1)->whereIn('id',$dishes_list);
		//If there is limit for dishes output
		if(!empty($quant)){
			$dishes = $dishes->limit($quant);
		}
		$dishes = $dishes->get();

		//Create dishes list
		$dishes_list = [];
		foreach($dishes as $i => $dish){
			$dishes_list[$i] = [
				'title'			=> $dish->title,
				'price'			=> (float)$dish->price
			];
			if($more_data == true){
				$dishes_list[$i]['calories'] = $dish->calories;
				$dishes_list[$i]['cooking_time'] = $dish->cooking_time;
				$dishes_list[$i]['dish_weight'] = $dish->dish_weight * 1000;
			}
		}
		//Sort dishes by price
		usort($dishes_list, function($a, $b){
			return $a['price'] > $b['price'];
		});

		return [
			'dish_count' => $dish_count,
			'dishes_list' => $dishes_list
		];
	}


	/**
	 * GET|HEAD /api/get_restaurants/{quant?}
	 * @param null|integer $quant - quantity of viewed dishes
	 * @return json string
	 */
	public function getAllRestaurants($quant = null){
		$restaurants = Restaurant::select('id','title','large_img','address','coordinates','rating')
			->where('enabled','=',1)
			->get();

		$content = [];
		foreach($restaurants as $restaurant){
			$dishes = $this->getDishes($restaurant->id, $quant);

			$large_img = json_decode($restaurant->large_img, true);
			$large_img = (!empty($large_img['src']))? asset($large_img['src']): '';

			$content[] = [
				'id'			=> $restaurant->id,
				'title'			=> $restaurant->title,
				'large_img'		=> $large_img,
				'address'		=> $restaurant->address,
				'coordinates'	=> $restaurant->coordinates,
				'rating'		=> $restaurant->rating,
				'rating_points'	=> $restaurant->rating['p'] - $restaurant->rating['n'],
				'dishes_count'	=> $dishes['dish_count'],
				'dishes'		=> $dishes['dishes_list'],
			];
		}

		usort($content, function($a, $b){
			return $a['rating_points'] < $b['rating_points'];
		});

		return json_encode($content);
	}


	/**
	 * GET|HEAD /api/get_restaurant/{id}/{quant?}
	 * @param $id \App\Restaurant ID
	 * @param null|integer $quant  - quantity of viewed dishes
	 * @return json string
	 */
	public function getRestaurant($id, $quant = null){
		$restaurant = Restaurant::select(
			'id','title','logo_img','large_img','text','address','work_time','coordinates','rating',
			'has_delivery','has_wifi','has_parking'
		)->where('enabled','=',1)->find($id);

		if(!empty($id) && !empty($restaurant)){
			$menus = MealMenu::select('dishes')
				->where('restaurant_id','=',$restaurant->id)
				->where('enabled','=',1)
				->get();
			//Create list of dishes IDs for current restaurant
			$dishes_list = [];
			foreach($menus as $menu){
				$dishes = json_decode($menu->dishes);
				$dishes_list = array_merge($dishes_list, $dishes);
			}
			$dishes_list = array_values(array_unique($dishes_list));
			//Get count of restaurant dishes
			$dish_count = count($dishes_list);

			//Get each dish data
			//If no need etc data for dish
			$dishes = MealDish::select('id','title','category_id','price','calories','cooking_time','dish_weight');

			$dishes = $dishes->where('enabled','=',1)->whereIn('id',$dishes_list)->orderBy('title','asc');
			//If there is limit for dishes output
			if(!empty($quant)){
				$dishes = $dishes->limit($quant);
			}
			$dishes = $dishes->get();

			//Create dishes list by kitchen type
			$kitchen_list = [];
			foreach($dishes as $i => $dish){
				$category = Category::select('id','title','position')->find($dish->category_id[0]);

				$kitchen_list[$category->id] = [
					'id'		=> $category->id,
					'title'		=> $category->title,
					'position'	=> $category->position,
				];
			}
			//Sorting categories by position
			usort($kitchen_list, function($a, $b){
				return $a['position'] > $b['position'];
			});

			//Asset logo img
			$logo_img = json_decode($restaurant->logo_img, true);
			$logo_img = (!empty($logo_img['src']))? asset($logo_img['src']): '';
			//Asset square im
			$large_img = json_decode($restaurant->large_img, true);
			$large_img = (!empty($large_img['src']))? asset($large_img['src']): '';

			$rated_visitors = VisitorsRates::select('visitor_id')
				->where('restaurant_id','=',$restaurant->id)
				->orderBy('created_at','desc')
				->limit(4)
				->get();

			$visitors = [];
			foreach($rated_visitors as $rated_visitor){
				$visitor = $rated_visitor->visitor()->select('img_url')->find($rated_visitor->visitor_id);
				$visitors[] = asset($visitor->img_url);
			}

			$content = [
				'title'			=> $restaurant->title,
				'logo_img'		=> $logo_img,
				'large_img'		=> $large_img,
				'text'			=> $restaurant->text,
				'address'		=> $restaurant->address,
				'work_time'		=> json_decode($restaurant->work_time),
				'coordinates'	=> $restaurant->coordinates,
				'rating'		=> $restaurant->rating,
				'vote_users'	=> $visitors,
				'has_delivery'	=> $restaurant->has_delivery,
				'has_wifi'		=> $restaurant->has_wifi,
				'has_parking'	=> $restaurant->has_parking,
				'dishes_count'	=> $dish_count,
				'kitchens'		=> $kitchen_list,
			];

			return json_encode($content);
		}else{
			return '[]';
		}
	}


	/**
	 * GET|HEAD /api/get_restaurants_by_filter/{request?}
	 * @param null|base_64(json(obj)) $request
	 * [
	 * 		kitchen_id - \App\Category ID (0 - all),
	 * 		price - max price of dish (0 - any price),
	 * 		title - possible restaurant title ('0' - any title),
	 * 		quant - max quantity of dishes (0 - all)
	 * ]
	 * @return json string
	 */
	public function getRestaurantsByFilter($request = null){
		$request = (!empty($request))? json_decode(base64_decode($request)): null;

		//Get restaurants
		$restaurants = \DB::table('restaurants')
			->select('id','title','large_img','address','coordinates','rating')
			->where('enabled','=',1);
		//Check for title filter
		if(isset($request->title) && ($request->title != '0')){
			$restaurants = $restaurants->where('title','LIKE','%'.$request->title.'%');
		}

		$restaurants = $restaurants->get();
		//Get content for restaurants
		$content = [];
		foreach($restaurants as $restaurant){
			//Get restaurant menus
			$menus = \DB::table('meal_menus')
				->select('dishes')
				->where('restaurant_id','=',$restaurant->id)
				->where('enabled','=',1)
				->get();

			//Search for dishes IDs in menu
			$dishes_list = [];
			foreach($menus as $menu){
				$dishes = json_decode($menu->dishes);
				$dishes_list = array_merge($dishes_list, $dishes);
			}
			$dishes_list = array_values(array_unique($dishes_list));
			//Get dishes total count for current restaurant
			$dishes_count = count($dishes_list);

			//Get dishes data
			$dishes = \DB::table('meal_dishes')
				->select('id','title','price')
				->where('enabled','=',1)
				->whereIn('id',$dishes_list);
			//Check for dish belongs to kitchen
			if(isset($request->kitchen_id) && ($request->kitchen_id > 0)){
				$dishes = $dishes->where('category_id','LIKE','%"'.$request->kitchen_id.'"%');
			}

			//Check for price limit
			if(isset($request->price) && ($request->price > 0)){
				$dishes = $dishes->where('price','<',$request->price);
			}

			//Get dishes limited quantity
			if(isset($request->quant) && ($request->quant > 0)){
				$dishes = $dishes->limit($request->quant);
			}
			$dishes = $dishes->get();

			//If restaurant has dishes by filter request
			if(!empty($dishes->all())){
				$dishes = $dishes->toArray();
				usort($dishes, function($a, $b){
					return $a->price > $b->price;
				});

				$large_img = json_decode($restaurant->large_img, true);
				$large_img = (!empty($large_img['src']))? asset($large_img['src']): '';

				$content[] = [
					'id'			=> $restaurant->id,
					'title'			=> $restaurant->title,
					'large_img'		=> $large_img,
					'address'		=> $restaurant->address,
					'coordinates'	=> json_decode($restaurant->coordinates),
					'rating'		=> json_decode($restaurant->rating, true),
					'dishes_count'	=> $dishes_count,
					'dishes'		=> $dishes
				];
			}
		}
		return json_encode($content);
	}
}