<?php
namespace App\Http\Controllers;


class ApiController extends Controller
{
	public function isJson($string){
		return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string)))))
			? true
			: false;
	}

	public function createImgBase64($img, $use_img_check = true){
		if(('undefined' != $img) && (!empty($img))){
			list($type, $img) = explode(';', $img);
			list(, $img) = explode(',', $img);
			$img = base64_decode($img);

			if ($use_img_check) {
				if(($type != 'data:image/png') && ($type != 'data:image/jpeg') && ($type != 'data:image/gif')){
					return '';
				}
			}
			$extension = explode('/', $type);
			$filename = '/user_img/' . uniqid() . '.' . $extension[1];
			$destinationPath = base_path() . '/public' . $filename;
			try{
				file_put_contents($destinationPath, $img);
			}catch(\Exception $e){
				return dump($e);
			}

			$img_resolution = getimagesize($destinationPath);
			$img_resolution = [
				'original' => [
					'width' => $img_resolution[0],
					'height'=> $img_resolution[1]
				],
				'modified' => [
					'width' => $img_resolution[0] * 2,
					'height'=> $img_resolution[1] * 2
				]
			];

			$image = Image::make($destinationPath);
			$image->resize($img_resolution['modified']['width'], $img_resolution['modified']['height']);
			$image->save($destinationPath);
			return $filename;
		}else{
			return 'Изображение не определено';
		}
	}
}