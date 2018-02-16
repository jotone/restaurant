<?php
namespace App\Http\Controllers\Api;

use App\Category;
use App\Comments;
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
				'coordinates'	=> [
					'latitude'		=> (float)$restaurant->coordinates['x'],
					'longitude'		=> (float)$restaurant->coordinates['y']
				],
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
			$dishes = MealDish::select('id','title','category_id','square_img','price','calories','cooking_time','dish_weight','model_3d');

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
				$dish = $dish->toArray();
				$dish['square_img'] = (!empty($dish['square_img']['src']))? asset($dish['square_img']['src']): '';
				unset($dish['category_id']);
				if(!isset($kitchen_list[$category->id])){
					$kitchen_list[$category->id] = [
						'id'		=> $category->id,
						'title'		=> $category->title,
						'position'	=> $category->position,
						'items'		=> [$dish]
					];
				}else{
					$kitchen_list[$category->id]['items'][] = $dish;
				}
			}
			//Sorting categories by position
			usort($kitchen_list, function($a, $b){
				return $a['position'] > $b['position'];
			});
			//Sorting dishes by title
			foreach($kitchen_list as $i => $item){
				usort($item['items'], function($a, $b){
					return strcmp($a['title'], $b['title']);
				});
				$kitchen_list[$i]['items'] = $item['items'];
			}

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
				'coordinates'	=> [
					'latitude'		=> (float)$restaurant->coordinates['x'],
					'longitude'		=> (float)$restaurant->coordinates['y']
				],
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
	 * @param null|base_64(json(obj)) $request
	 * [
	 * 		kitchen_id - \App\Category ID (0 - all),
	 * 		price - max price of dish (0 - any price),
	 * 		title - possible restaurant or dish title ('0' - any title),
	 * 		quant - max quantity of dishes (0 - all)
	 * ]
	 * @return json string
	 */
	public static function getRestaurantsByFilterStatic($request = null){
		$request = (!empty($request))? json_decode(base64_decode($request)): null;

		$dish_list = [];

		$dishes = \DB::table('meal_dishes')
			->select('id','title','price')
			->where('enabled','=',1);

		//Check for dish belongs to kitchen
		if(isset($request->kitchen_id) && ($request->kitchen_id > 0)){
			$dishes = $dishes->where('category_id','LIKE','%"'.$request->kitchen_id.'"%');
		}

		//Check for price limit
		if(isset($request->price) && ($request->price > 0)){
			$dishes = $dishes->where('price','<',$request->price);
		}

		//Check for title request
		if( isset($request->title) && (trim($request->title) != '')){
			$dishes = $dishes->where('title','LIKE','%'.$request->title.'%');
		}
		$dishes = $dishes->get();

		$restaurant_ids = [];
		foreach($dishes as $dish){
			$menus = \DB::table('meal_menus')
				->select('id','restaurant_id')
				->where('dishes','LIKE','%"'.$dish->id.'"%')
				->where('enabled','=',1)
				->get();
			foreach($menus as $menu){
				$restaurant_ids[] = $menu->restaurant_id;

				$dish_list[$menu->restaurant_id][] = $dish;
			}

		}

		$restaurant_ids = array_values(array_unique($restaurant_ids));

		$restaurants = \DB::table('restaurants')
			->select('id','title','large_img','address','coordinates','rating')
			->where('enabled','=',1)
			->whereIn('id',$restaurant_ids);

		//Check for title filter
		if(isset($request->title) && (trim($request->title) != '')){
			$restaurants = $restaurants->orWhere('title','LIKE','%'.$request->title.'%');
		}
		$restaurants = $restaurants->get();

		$content = [];

		foreach($restaurants as $restaurant){
			$large_img = json_decode($restaurant->large_img, true);
			$large_img = (!empty($large_img['src']))? asset($large_img['src']): '';

			/*$inner_dishes = (isset($dish_list[$restaurant->id]))
				? $dish_list[$restaurant->id]
				: [];
			$dishes_count = count($inner_dishes);

			if(empty($inner_dishes)){*/
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
			$inner_dishes = \DB::table('meal_dishes')
				->select('id','title','price')
				->where('enabled','=',1)
				->whereIn('id',$dishes_list);

			//Check for dish belongs to kitchen
			if(isset($request->kitchen_id) && ($request->kitchen_id > 0)){
				$inner_dishes = $inner_dishes->where('category_id','LIKE','%"'.$request->kitchen_id.'"%');
			}

			//Check for price limit
			if(isset($request->price) && ($request->price > 0)){
				$inner_dishes = $inner_dishes->where('price','<',$request->price);
			}

			//Get dishes limited quantity
			if(isset($request->quant) && ($request->quant > 0)){
				$inner_dishes = $inner_dishes->limit($request->quant);
			}
			$inner_dishes = $inner_dishes->get();

			$inner_dishes = $inner_dishes->toArray();
			/*}else{

			}*/

			usort($inner_dishes, function($a, $b){
				return $a->price > $b->price;
			});

			$coords = json_decode($restaurant->coordinates, true);
			$content[] = [
				'id'			=> $restaurant->id,
				'title'			=> $restaurant->title,
				'large_img'		=> $large_img,
				'address'		=> $restaurant->address,
				'coordinates'	=> [
					'latitude'		=> (float)$coords['x'],
					'longitude'		=> (float)$coords['y']
				],
				'rating'		=> json_decode($restaurant->rating, true),
				'dishes_count'	=> $dishes_count,
				'dishes'		=> $inner_dishes
			];
		}

		return json_encode($content);
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
		return self::getRestaurantsByFilterStatic($request);
	}


	/**
	 * GET|HEAD /api/get_restaurant_reviews/{id}
	 * @param $id \App\Restaurant
	 * @return string
	 */
	public function getRestaurantReviews($id){
		$reviews = [];
		$comments = Comments::select('user_id','text','created_at','type')
			->where('post_id','=',$id)
			->orderBy('created_at','desc')
			->limit(50)
			->get();

		foreach($comments as $comment){
			$user = $comment->user()->select('name','surname','img_url')->first();

			$reviews[] = [
				'user' => [
					'name' => $user->name,
					'surname' => $user->surname,
					'img_url' => asset($user->img_url)
				],
				'comment' => [
					'text' => $comment->text,
					'date' => date('d.m.Y', strtotime($comment->created_at)),
					'rating' => $comment->type
				]
			];
		}

		return json_encode($reviews);
	}
}