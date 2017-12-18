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
							$data[$j] = $item->val;
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
}