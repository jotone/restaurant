<?php
namespace App\Http\Controllers\Api;

use App\Category;
use App\MealDish;
use App\MealMenu;
use App\Restaurant;

use App\Http\Controllers\ApiController;

class RestaurantController extends ApiController
{
	/**
	 * @param \App\MealMenu $menus
	 * @return array
	 */
	protected function getKitchens($menus){
		//Create restaurant dishes array
		$dishes = [];
		foreach($menus as $menu){
			$menu_dishes = ($this->isJson($menu->dishes))? json_decode($menu->dishes): [];
			foreach($menu_dishes as $dish){
				$dishes[] = $dish;
			}
		}
		//Get unique dishes ids
		$dishes = array_values(array_unique($dishes));

		//Make kitchen types array
		$kitchen_types = [];

		foreach($dishes as $dish_id){
			//Search for dish data
			$dish = MealDish::select('category_id','price')
				->where('enabled','=',1)
				->find($dish_id);
			foreach($dish->category_id as $category_id){
				//Get current price for dish
				$current_price = (!empty($dish->price))? $dish->price: 0;

				if(!isset($kitchen_types[$category_id])){
					//Create kitchen type
					$category = Category::select('title')->find($category_id);
					if(!empty($category)){
						$kitchen_types[$category_id] = [
							'title' => $category->title,
							'min_price' => $current_price
						];
					}
				}else{
					//If isset kitchen -> get min price
					if($kitchen_types[$category_id]['min_price'] > $current_price){
						$kitchen_types[$category_id]['min_price'] = $current_price;
					}
				}
			}
		}

		return $kitchen_types;
	}


	/**
	 * GET|HEAD /api/get_restaurants
	 * Get all the restaurants
	 * @return string
	 */
	public function getAll(){
		$restaurants = Restaurant::select(
			'id','title','logo_img','square_img','large_img','address','coordinates','rating'
		)->where('enabled','=',1)->get();

		$content = [];
		foreach($restaurants as $restaurant){

			$logo = ($this->isJson($restaurant->logo_img))
				? json_decode($restaurant->logo_img)
				: null;
			$square_img = ($this->isJson($restaurant->square_img))
				? json_decode($restaurant->square_img)
				: null;
			$large_img = ($this->isJson($restaurant->large_img))
				? json_decode($restaurant->large_img)
				: null;

			//Get menus
			$menus = $restaurant->mealMenus()->select('dishes')->get();

			$restaurant = $restaurant->toArray();

			$restaurant['kitchen_type'] = $this->getKitchens($menus);

			$content[] = [
				'id'		=> $restaurant['id'],
				'title'		=> $restaurant['title'],
				'location'	=> [
					'text'		=> $restaurant['address'],
					'coords'	=> $restaurant['coordinates']
				],
				'images'	=> [
					'logo'		=> (!empty($logo))
						?	['src'		=> asset($logo->src),
							'width'		=> $logo->width,
							'height'	=> $logo->height]
						: null,
					'square'	=> (!empty($square_img))
						?	['src'		=> asset($square_img->src),
							'width'		=> $square_img->width,
							'height'	=> $square_img->height]
						: null,
					'large'		=> (!empty($large_img))
						?	['src'		=> asset($large_img->src),
							'width'		=> $large_img->width,
							'height'	=> $large_img->height]
						: null,
				],
				'like_bar'	=> $restaurant['rating'],
				'kitchen_type' => $restaurant['kitchen_type']
			];
		}

		return json_encode($content);
	}


	/**
	 * GET|HEAD /api/get_restaurants_by_kitchen/{kitch_id}
	 * @param $kitch_id \App\Category ID
	 * @return string
	 */
	public function getByKitchen($kitch_id){
		//Get dishes list by kitchen
		$dishes = MealDish::select('id')
			->where('enabled','=',1)
			->where('category_id','LIKE','%"'.$kitch_id.'"%')
			->get();

		//Get restaurant ids
		$restaurants_list = [];

		foreach($dishes as $dish){
			$meal_menu = MealMenu::select('restaurant_id')
				->where('enabled','=',1)
				->where('dishes','LIKE','%"'.$dish->id.'"%')
				->get();

			foreach($meal_menu as $item){
				$restaurants_list[] = $item->restaurant_id;
			}
		}

		$restaurants_list = array_values(array_unique($restaurants_list));

		//Get restaurant data
		$restaurants = Restaurant::select('id','title','logo_img','large_img','address','rating')
			->where('enabled','=',1)
			->whereIn('id',$restaurants_list)
			->get();

		foreach($restaurants as $i => $restaurant){
			$logo = json_decode($restaurant->logo_img, true);
			$logo['src'] = (!empty($logo['src']))? asset($logo['src']): '';
			$restaurant->logo_img = $logo;

			$large = json_decode($restaurant->large_img, true);
			$large['src'] = (!empty($large['src']))? asset($large['src']): '';
			$restaurant->large_img = $large;

			$restaurants[$i] = $restaurant;
		}
		if(!empty($restaurants)){
			return json_encode($restaurants->toArray());
		}
	}


	/**
	 * GET|HEAD /api/get_restaurant/{id}
	 * Get restaurant by ID
	 * @param $id
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|string|\Symfony\Component\HttpFoundation\Response
	 */
	public function getOne($id){
		$restaurant = Restaurant::select(
			'id','title','text','logo_img','large_img','square_img','address','work_time','has_delivery','has_wifi','coordinates',
			'rating'
		)->find($id);

		if(empty($restaurant)){
			return response(json_encode([
				'message' => 'Запрашиваемый ресторан отсутствует.'
			]), 400);
		}

		//Convert work time
		$restaurant->work_time = json_decode($restaurant->work_time, true);

		//Convert images objects to arrays
		$logo = json_decode($restaurant->logo_img, true);
		$logo['src'] = (!empty($logo['src']))? asset($logo['src']): '';
		$restaurant->logo_img = $logo;

		$large = json_decode($restaurant->large_img, true);
		$large['src'] = (!empty($large['src']))? asset($large['src']): '';
		$restaurant->large_img = $large;

		$square = json_decode($restaurant->square_img, true);
		$square['src'] = (!empty($square['src']))? asset($square['src']): '';
		$restaurant->square_img = $square;

		//Get restaurant menus
		$menus = $restaurant->mealMenus()->select('dishes')->get();
		//Convert restaurant object to array
		$restaurant = $restaurant->toArray();

		$restaurant['kitchen_type'] = $this->getKitchens($menus);

		return json_encode($restaurant);
	}
}