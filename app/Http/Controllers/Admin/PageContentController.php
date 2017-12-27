<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AppController;
use App\Http\Controllers\Controller;
use App\PageContent;

class PageContentController extends Controller
{
	/**
	 * Transform page content into proper
	 * @param $data Content Array
	 * @return array Encoded Array
	 */
	public static function processData($data){
		switch($data->type){
			case 'slider':
				$value = [];
				foreach($data->val as $image){
					$value[] = [
						'src' => ($image->type == 'upload')
							? AppController::createImgBase64Static($image->src)
							: $image->src,
						'alt' => $image->alt
					];
				}
				$value = json_encode($value);
				break;
			case 'single-image':
				$value = json_encode([
					'src' => ($data->move == 'upload')
						? AppController::makeSquareImageStatic(AppController::createImgBase64Static($data->val))
						: $data->val,
					'alt' => ''
				]);
				break;
			case 'custom-slider':
				$value = [];
				foreach($data->val as $slide){
					$slide_data = [];
					foreach($slide as $content){
						$slide_data[] = [
							'type'=> $content->type,
							'key'=> $content->key,
							'val'=> self::processData($content)
						];
					}
					$value[] = $slide_data;
				}
				$value = json_encode($value);
				break;
			case 'categories-list':
				$value = json_encode($data->val);
				break;
			default:
				$value = $data->val;
		}
		return $value;
	}


	/**
	 * GET|HEAD /admin/page_content/{id}
	 * @param $id \App\Page ID
	 * @return json array
	 */
	public function getContentData($id){
		$content = PageContent::select('type','meta_key','meta_val')->where('page_id','=',$id)->get();
		$content_list = [];
		foreach($content as $item){
			switch($item->type){
				case 'slider':
				case 'custom-slider':
				case 'single-image':
					$val = json_decode($item->meta_val);
				break;
				default: $val = $item->meta_val;
			}
			$content_list[] = [
				'type'	=> $item->type,
				'key'	=> $item->meta_key,
				'val'	=> $val
			];
		}
		return json_encode([
			'message' => 'success',
			'content' => $content_list
		]);
	}


	/**
	 * Create PageContent
	 * @param $page_id \App\Page ID
	 * @param $data
	 * @return mixed \App\PageContent ID
	 */
	public static function store($page_id, $data){
		$value = self::processData($data);

		$result = PageContent::create([
			'type'		=> $data->type,
			'meta_key'	=> $data->key,
			'meta_val'	=> $value,
			'page_id'	=> $page_id
		]);
		if($result != false){
			return $result->id;
		}
	}


	/**
	 * Upadate PageContent
	 * @param $page_id \App\Page ID
	 * @param $data
	 * @return bool
	 */
	public static function update($page_id, $data){
		$value = self::processData($data);
		$result = PageContent::where('page_id','=',$page_id)
			->where('meta_key','=',$data->key)
			->update([
				'type'		=> $data->type,
				'meta_val'	=> $value
			]);
		return ($result != false);
	}


	/**
	 * Delete PageContent by page ID
	 * @param $id \App\Page ID
	 * @return bool
	 */
	public static function destroy($id){
		PageContent::where('page_id','=',$id)->delete();
	}
}