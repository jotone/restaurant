<?php
namespace App\Http\Controllers\Api;

use App\Category;
use App\Settings;

use App\Http\Controllers\ApiController;

class KitchenController extends ApiController
{
	/**
	 * GET|HEAD /api/get_kitchens
	 * @return json string
	 */
	public function getKitchens(){
		$kitchen_settings = Settings::select('options')->where('slug','=','dish')->first();

		$kitchen_settings = json_decode($kitchen_settings->options);

		$category_type = $kitchen_settings->category_type;

		$categories = Category::select('id','title','img_url')
			->where('category_type','=',$category_type)
			->where('enabled','=',1)
			->get();
		foreach($categories as $category){
			$img = json_decode($category->img_url);
			$category->img_url = (!empty($img->src))? asset($img->src): '';
		}

		if(!empty($categories->all())){
			$categories = $categories->toArray();
			return json_encode($categories);
		}else{
			return '[]';
		}
	}
}