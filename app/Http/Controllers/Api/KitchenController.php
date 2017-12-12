<?php
namespace App\Http\Controllers\Api;

use App\Category;
use App\MealDish;

use App\Http\Controllers\ApiController;

class KitchenController extends ApiController
{
	public function getAll(){
		$dishes = MealDish::where('enabled','=',1)->get();
		$categories_list = [];
		foreach($dishes as $dish){
			foreach($dish->category_id as $category_id){
				$category = Category::select('id','title')->find($category_id);
				if(!empty($category)){
					if(!isset($categories_list[$category->id])){
						$categories_list[$category->id] = [
							'title' => $category->title,
							'items' => [
								$dish->id => [
									'id' => $dish->id,
									'title' => $dish->title,
									'price' => (float)$dish->price,
									'square_img' => $dish->square_img,
									'large_img' => $dish->large_img,
								]
							]
						];
					}else{
						$categories_list[$category->id]['items'][$dish->id] = [
							'id' => $dish->id,
							'title' => $dish->title,
							'price' => (float)$dish->price,
							'square_img' => $dish->square_img,
							'large_img' => $dish->large_img,
						];
					}
				}
			}
		}
		foreach($categories_list as $i => $item){
			usort($item['items'], function($a, $b){
				return $b['price'] < $a['price'];
			});
			$categories_list[$i] = $item;
		}

		return json_encode($categories_list);
	}
}