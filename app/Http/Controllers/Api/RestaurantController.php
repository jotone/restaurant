<?php
namespace App\Http\Controllers\Api;

use App\MealDish;
use App\MealMenu;
use App\Restaurant;

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
			$large_img['src'] = (!empty($large_img['src']))? asset($large_img['src']): '';

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
			'title','logo_img','square_img','text','address','work_time','coordinates','rating',
			'has_delivery','has_wifi','has_parking'
		)->where('enabled','=',1)->find($id);

		if(!empty($id) && !empty($restaurant)){
			$dishes = $this->getDishes($id, $quant, true);

			$logo_img = json_decode($restaurant->logo_img, true);
			$logo_img['src'] = (!empty($logo_img['src']))? asset($logo_img['src']): '';

			$square_img = json_decode($restaurant->square_img, true);
			$square_img['src'] = (!empty($square_img['src']))? asset($square_img['src']): '';

			$content = [
				'title'			=> $restaurant->title,
				'large_img'		=> $logo_img,
				'square_img'	=> $square_img,
				'text'			=> $restaurant->text,
				'address'		=> $restaurant->address,
				'work_time'		=> json_decode($restaurant->work_time),
				'coordinates'	=> $restaurant->coordinates,
				'rating'		=> $restaurant->rating,
				'has_delivery'	=> $restaurant->has_delivery,
				'has_wifi'		=> $restaurant->has_wifi,
				'has_parking'	=> $restaurant->has_parking,
				'dishes_count'	=> $dishes['dish_count'],
				'dishes'		=> $dishes['dishes_list'],
			];

			return json_encode($content);
		}else{
			return '[]';
		}
	}


	/**
	 * GET|HEAD /api/get_restaurants_by_filter/kitchen/{kitchen_id}/price/{price}/title/{title}/quant/{quant}
	 * @param integer $kitchen_id - \App\Category ID
	 * @param integer $price - max dish price
	 * @param string $title - possible title of restaurant
	 * @param integer $quant  - quantity of viewed dishes
	 * @return json string
	 */
	public function getRestaurantsByFilter($request = '[]'){
		$request = json_decode(base64_decode($request));

		//Get restaurants
		$restaurants = \DB::table('restaurants')
			->select('id','title','large_img','address','coordinates','rating')
			->where('enabled','=',1);
		//Check for title filter
		if(isset($request->title)){
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
			if(isset($request->kitchen_id)){
				$dishes = $dishes->where('category_id','LIKE','%"'.$request->kitchen_id.'"%');
			}

			//Check for price limit
			if(isset($request->price)){
				$dishes = $dishes->where('price','<',$request->price);
			}

			//Get dishes limited quantity
			if(isset($request->quant)){
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
				$large_img['src'] = (!empty($large_img['src']))? asset($large_img['src']): '';

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