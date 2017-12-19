<?php
namespace App\Http\Controllers\Api;

use App\Category;
use App\MealDish;
use App\MealMenu;

use App\Http\Controllers\ApiController;
use App\Settings;

class KitchenController extends ApiController
{
	/**
	 * @param array of \App\MealDish $dishes
	 * @return array
	 */
	protected function createCategoriesList($dishes){
		$categories_list = [];
		foreach($dishes as $dish){
			//Get dish categories
			foreach($dish->category_id as $category_id){
				//Get category data
				$category = Category::select('id','title')->find($category_id);

				if(!empty($category)){
					$large = $dish->large_img;
					$large['src'] = (!empty($large['src']))
						? asset($large['src'])
						: '';

					$square = $dish->square_img;
					$square['src'] = (!empty($square['src']))
						? asset($square['src'])
						: '';

					if(!isset($categories_list[$category->id])){
						//If there is no such category in result array
						$categories_list[$category->id] = [
							'title' => $category->title,
							'items' => [
								$dish->id => [
									'id'		=> $dish->id,
									'title'		=> $dish->title,
									'price'		=> (float)$dish->price,
									'square_img'=> $square,
									'large_img'	=> $large,
								]
							]
						];
					}else{
						//If there is such category -> add dish to it's items
						$categories_list[$category->id]['items'][$dish->id] = [
							'id'		=> $dish->id,
							'title'		=> $dish->title,
							'price'		=> (float)$dish->price,
							'square_img'=> $square,
							'large_img'	=> $large,
						];
					}

				}
			}
		}
		//Sort dishes by price
		foreach($categories_list as $i => $item){
			usort($item['items'], function($a, $b){
				return $b['price'] < $a['price'];
			});
			$categories_list[$i] = $item;
		}

		return $categories_list;
	}


	/**
	 * GET|HEAD /api/get_kitchen
	 * Get all categories
	 * @return string
	 */
	public function getAll(){
		//Get all dishes list
		$dishes = MealDish::select('id','title','price','square_img','large_img','category_id')
			->where('enabled','=',1)
			->get();

		$categories_list = $this->createCategoriesList($dishes);

		return json_encode($categories_list);
	}


	/**
	 * GET|HEAD /api/get_kitchen/{rest_id}
	 * Get restaurant categories by restaurant ID
	 * @param $rest_id \App\Restaurant ID
	 * @return string
	 */
	public function getByRestaurant($rest_id){
		//Get meal menus for current restaurant
		$meal_menu = MealMenu::select('dishes')->where('restaurant_id','=',$rest_id)->where('enabled','=',1)->get();

		$dishes_list = [];

		foreach($meal_menu as $item){
			$dishes = ($this->isJson($item->dishes))? json_decode($item->dishes): [];
			$dishes_list = array_merge($dishes_list, $dishes);
		}

		$dishes_list = array_values(array_unique($dishes_list));
		//Get dishes list
		$dishes = MealDish::select('id','title','price','square_img','large_img','category_id')
			->where('enabled','=',1)
			->whereIn('id',$dishes_list)
			->get();

		$categories_list = $this->createCategoriesList($dishes);

		return json_encode($categories_list);
	}


	/**
	 * GET|HEAD /get_kitchen/{rest_id}/kitchen/{kitch_id}
	 * Get restaurant categories by restaurant ID and category ID
	 * @param $rest_id \App\Restaurant ID
	 * @param $kitch_id \App\Category ID
	 * @return string
	 */
	public function getConcrete($rest_id, $kitch_id){
		//Get meal menus for current restaurant
		$meal_menu = MealMenu::select('dishes')->where('restaurant_id','=',$rest_id)->where('enabled','=',1)->get();

		$dishes_list = [];

		foreach($meal_menu as $item){
			$dishes = ($this->isJson($item->dishes))? json_decode($item->dishes): [];
			$dishes_list = array_merge($dishes_list, $dishes);
		}

		$dishes_list = array_values(array_unique($dishes_list));
		//Get dishes list by it category
		$dishes = MealDish::select('id','title','price','square_img','large_img','category_id')
			->where('enabled','=',1)
			->whereIn('id',$dishes_list)
			->where('category_id','LIKE','%"'.$kitch_id.'"%')
			->get();

		$categories_list = $this->createCategoriesList($dishes);

		return json_encode($categories_list);
	}


	/**
	 * GET|HEAD /api/get_filter_kitchens
	 * @return string
	 */
	public function getFilterKitchens(){
		$settings = Settings::select('options')->where('slug','=','dish')->first();
		$settings = json_decode($settings->options);

		$categories = Category::select('id','title','img_url')
			->where('category_type','=',$settings->category_type)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();

		$content = [];
		foreach($categories as $category){
			$img = ($this->isJson($category->img_url))? json_decode($category->img_url): null;
			$content[] = [
				'id' => $category->id,
				'title' => $category->title,
				'img_url' => (isset($img->src) && !empty($img->src))? asset($img->src): ''
			];
		}

		return json_encode($content);
	}
}