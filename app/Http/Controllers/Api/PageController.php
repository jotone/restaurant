<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\PageContent;
use App\Pages;
use Illuminate\Http\Request;

class PageController extends ApiController
{
	/**
	 * GET|HEAD /api/get_page_data/{slug}
	 * @param $slug \App\Pages SLUG
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|string|\Symfony\Component\HttpFoundation\Response
	 */
	public function getPageData($slug){
		if(empty($slug)){
			return response(404);
		}

		$page = Pages::select('id')->where('enabled','=',1)->where('slug','=',$slug)->first();
		$page_content = $page->content()->select('meta_key','meta_val')->get();

		$content_list = [];
		foreach($page_content as $page_value){
			if($this->isJson($page_value->meta_val)){
				//If value is json
				$temp = json_decode($page_value->meta_val);

				if(isset($temp->src)){
					//If value is json-encoded image
					$content_list[$page_value->meta_key] = asset($temp->src);
				}else{
					//If value is a slider
					foreach($temp as $i => $items){
						foreach($items as $j => $item){
							if(isset($item->val)){
								if($this->isJson($item->val)){
									$item->val = json_decode($item->val);
									if(isset($item->val->src)){
										$item->val = asset($item->val->src);
									}
								}
								$data[$j] = $item->val;
							}else{
								$data[$j] = $item;
							}
						}
						$content_list[$page_value->meta_key][$i] = $data;
					}
				}
			}else{
				//If value is a text
				$content_list[$page_value->meta_key] = $page_value->meta_val;
			}
		}

		return json_encode($content_list);
	}


	/**
	 * GET|HEAD /api/get_page_data/restaurant_recomended
	 * @return string
	 */
	public function getRecommendedList(){
		$page = Pages::select('id')->where('enabled','=',1)->where('slug','=','restaurant_recomended')->first();
		$page_content = $page->content()->select('meta_key','meta_val')->first();

		$content = json_decode($page_content->meta_val);
		$ids = [];
		foreach($content as $item){
			$ids[] = $item->id;
		}

		//Get restaurants
		$restaurants = \DB::table('restaurants')
			->select('id','title','large_img','address','coordinates','rating')
			->where('enabled','=',1)
			->whereIn('id',$ids)
			->get();

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
				->whereIn('id',$dishes_list)
				->limit(3)
				->get();

			//If restaurant has dishes by filter request
			if(!empty($dishes->all())){
				$dishes = $dishes->toArray();

				usort($dishes, function($a, $b){
					return $a->price > $b->price;
				});

				$large_img = json_decode($restaurant->large_img, true);
				$large_img = (!empty($large_img['src']))? asset($large_img['src']): '';

				$coordinates = json_decode($restaurant->coordinates, true);
				$content[] = [
					'id'			=> $restaurant->id,
					'title'			=> $restaurant->title,
					'large_img'		=> $large_img,
					'address'		=> $restaurant->address,
					'coordinates'	=> [
						'latitude'		=> (float)$coordinates['x'],
						'longitude'		=> (float)$coordinates['y']
					],
					'rating'		=> json_decode($restaurant->rating, true),
					'dishes_count'	=> $dishes_count,
					'dishes'		=> $dishes
				];
			}
		}

		return json_encode($content);
	}
}