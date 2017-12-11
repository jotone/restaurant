<?php
namespace App\Http\Controllers\Api;

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
					'large'		=> (!empty($restaurant->square_img))
						?	['src'		=> asset($restaurant->large_img->src),
							'width'		=> $restaurant->large_img->width,
							'height'	=> $restaurant->large_img->height]
						: null,
				],
				'kitchen_type' => [],
				'like_bar'	=> $restaurant->rating
			];
		}
		return json_encode($restaurants->toArray());
	}
}