<?php
namespace App\Http\Controllers\Admin;

use App\Tags;
use App\Http\Controllers\AppController;
class TagController extends AppController
{
	/**
	 * @param $tag tag title
	 * @return integer ID of tag
	 */
	public static function createTag($tag){
		$slug = str_slug(trim($tag));
		if(!empty($slug)){
			if (Tags::select('id')->where('slug', '=', $slug)->count() == 0) {
				$result = Tags::create([
					'title' => trim($tag),
					'slug' => $slug
				]);
			} else {
				$result = Tags::select('id')->where('slug', '=', $slug)->first();
			}
			return $result->id;
		}
	}
}