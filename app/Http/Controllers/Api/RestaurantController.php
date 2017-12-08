<?php
namespace App\Http\Controllers\Api;

use App\Restaurant;

use \App\Http\Controllers\ApiController;


class RestaurantController extends ApiController
{
	public function getAll(){
		$restaurants = Restaurant::where('enabled','=',1)->get();
		$content = [];
		foreach($restaurants as $restaurant){
			$content[] = [
				'id'		=> $restaurant->id,
				'title'		=> $restaurant->title,
				'location'	=> [
					'text'		=> $restaurant->address,
					'coords'	=> $restaurant->coordinates
				],
				'image'		=> [],
				'kitchen_type' => [],
				'like_bar'	=> []
			];
		}
		return json_encode($restaurants->toArray());
	}
}