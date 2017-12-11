<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;

use App\Http\Controllers\AppController;
use App\Restaurant;
use Illuminate\Http\Request;

class GalleryController extends AppController
{
	/**
	 * GET|HEAD /admin/settings/gallery
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug','=',$current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, '');

			//Get file list
			$files = self::getFolders();
			$images_usage = [];
			foreach($files as $file){
				$used_id = [];
				$temp = $this->getFileName($file);

				//Check for image is in category
				$categories = Category::select('id','title','img_url')
					->where('img_url','LIKE','%'.$temp.'%')
					->get();
				//Create array of image usage
				foreach($categories as $category){
					$used_id['category'][$category->id] = $category->title;
				}

				//Check for image in restaurants
				$restaurants = Restaurant::select('id','title','large_img','logo_img','square_img')
					->where('logo_img','LIKE','%'.$temp.'%')
					->orWhere('large_img','LIKE','%'.$temp.'%')
					->orWhere('square_img','LIKE','%'.$temp.'%')
					->get();
				//Create array of image usage
				foreach($restaurants as $restaurant){
					$used_id['restaurant'][$restaurant->id] = $restaurant->title;
				}

				$images_usage[] = [
					'src' => $file,
					'used_in' => $used_id
				];
			}

			return view('admin.gallery', [
				'start'			=> $start,
				'page'			=> $current_page,
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> $page->title,
				'content'		=> $images_usage
			]);
		}
	}


	/**
	 * GET /admin/settings/gallery/all
	 * Function gets all images from /public/img
	 * @return string json images array
	 */
	public function all(){
		$files = self::getFolders();
		$result = [];
		foreach($files as $file){
			$name = $this->getFileName($file);
			$result[] = [
				'src'	=> $file,
				'name'	=> $name,
				'size'	=> self::niceFilesize($file)
			];
		}
		return json_encode([
			'message'	=> 'success',
			'images'	=> $result
		]);
	}


	/**
	 * POST /admin/settings/gallery/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string json obj
	 */
	public function create(Request $request){
		$data = $request->all();
		//If image was sent by ajax request
		if(isset($data['ajax'])){
			if(isset($data['upload'])){
				$result = $this->createImgBase64($data['upload']);
				//Image wasn't created
				if(empty($result)){
					return json_encode([
						'message'	=> 'error',
						'text'		=> 'Image is corrupted or has unacceptable mime-type'
					]);
				}else{
					$name = $this->getFileName($result);
					return json_encode([
						'message'	=> 'success',
						'text'		=> 'Image '.$name.' was saved successfully',
						'image'		=> [
							'src'		=> $result,
							'name'		=> $name,
							'size'		=> self::niceFilesize(base_path().'/public/'.$result)
						]
					]);
				}
			}
		//If images was sent by form request
		}else{
			$error = '';
			if(!empty($request->file())){
				$files = $request->file('upload');
				foreach($files as $file){
					if($file->isValid()){
						//Save image
						$result = $this->createImg($file);
						if(empty($result)){
							$error .= '<p>Image '.$file->getClientOriginalName().' was not created</p>';
						}
					}
				}
			}
			if(empty($error)){
				return redirect()->route('admin.gallery.index');
			}else{
				return redirect()->route('admin.gallery.index')->withErrors($error);
			}
		}
	}


	/**
	 * DELETE /admin/settings/gallery/drop_unused
	 * @param \Illuminate\Http\Request $request
	 * @return string json message
	 */
	public function dropUnused(Request $request){
		$data = $request->all();
		foreach($data['files'] as $file){
			$path = base_path().'/public'.$file;
			$result = unlink($path);
		}
		if($result){
			return json_encode(['message' => 'success']);
		}
	}


	/**
	 * DELETE /admin/settings/gallery/{image}
	 * @param string $image filename of image
	 * @return string json message
	 */
	public function destroy($image){
		$path = base_path().'/public/img/'.$image;
		$result = unlink($path);
		if($result){
			return json_encode(['message' => 'success']);
		}
	}
}