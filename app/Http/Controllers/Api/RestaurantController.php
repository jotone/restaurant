<?php
namespace App\Http\Controllers\Api;

use App\Category;
use App\MealDish;
use App\MealMenu;
use App\Restaurant;

use \App\Http\Controllers\ApiController;


class RestaurantController extends ApiController
{
	public function getAll(){
		$restaurants = Restaurant::select(
			'id','title','logo_img','square_img','large_img','address','coordinates','rating'
		)->where('enabled','=',1)
			->get();
		$content = [];
		foreach($restaurants as $restaurant){
			$restaurant->logo_img = ($this->isJson($restaurant->logo_img))
				? json_decode($restaurant->logo_img)
				: null;
			$restaurant->square_img = ($this->isJson($restaurant->square_img))
				? json_decode($restaurant->square_img)
				: null;
			$restaurant->large_img = ($this->isJson($restaurant->large_img))
				? json_decode($restaurant->large_img)
				: null;

			$content[] = [
				'id'		=> $restaurant->id,
				'title'		=> $restaurant->title,
				'location'	=> [
					'text'		=> $restaurant->address,
					'coords'	=> $restaurant->coordinates
				],
				'images'	=> [
					'logo'		=> (!empty($restaurant->logo_img))
						?	['src'		=> asset($restaurant->logo_img->src),
							'width'		=> $restaurant->logo_img->width,
							'height'	=> $restaurant->logo_img->height]
						: null,
					'square'	=> (!empty($restaurant->square_img))
						?	['src'		=> asset($restaurant->square_img->src),
							'width'		=> $restaurant->square_img->width,
							'height'	=> $restaurant->square_img->height]
						: null,
					'large'		=> (!empty($restaurant->large_img))
						?	['src'		=> asset($restaurant->large_img->src),
							'width'		=> $restaurant->large_img->width,
							'height'	=> $restaurant->large_img->height]
						: null,
				],
				'like_bar'	=> $restaurant->rating
			];
		}

		return json_encode($content);
	}

	public function getOne($id){
		$restaurant = Restaurant::select(
			'id','title','text','logo_img','large_img','address','work_time','has_delivery','has_wifi','coordinates',
			'rating'
		)->find($id);

		if(empty($restaurant)){
			return response(json_encode([
				'message' => 'Запрашиваемый ресторан отсутствует.'
			]), 400);
		}

		//Convert images objects to arrays
		$restaurant->logo_img = json_decode($restaurant->logo_img, true);
		$restaurant->large_img = json_decode($restaurant->large_img, true);
		//Get restaurant menus
		$menus = $restaurant->mealMenus()->select('dishes')->get();
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

		//Convert restaurant object to array
		$restaurant = $restaurant->toArray();
		//Make dishes array
		$restaurant['dishes'] = [];

		foreach($dishes as $dish_id){
			//Search for dish data
			$dish = MealDish::select('title','category_id','square_img','large_img','price','dish_weight','text','cooking_time')
				->where('enabled','=',1)
				->find($dish_id);

			if(!empty($dish)){
				$dish = $dish->toArray();
				$categories_list = [];
				//Get dish category
				foreach($dish['category_id'] as $category_id){
					$category = Category::select('title')->find($category_id);
					$categories_list[] = (!empty($category))
						? $category->title
						: null;
				}
				$dish['category_id'] = $categories_list;
				$restaurant['dishes'][] = $dish;
			}
		}

		return json_encode($restaurant);
	}
}