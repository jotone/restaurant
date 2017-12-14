<?php
namespace App\Http\Controllers;

use App\AdminMenu;
use App\Category;
use App\Roles;

use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use Auth;

class AppController extends Controller implements CrudInterface
{
	public function index(Request $request){return abort(404);}
	public function create(Request $request){return abort(404);}
	public function show($id, Request $request){return abort(404);}
	public function edit($id, Request $request){return abort(404);}
	public function store(Request $request){return abort(404);}
	public function update($id, Request $request){return abort(404);}
	public function destroy($id){return abort(404);}

	public function rus2translit($string) {
		//Массив трансформации букв
		$converter = array(
			'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e',
			'ё'=>'e', 'ж'=>'zh', 'з'=>'z', 'и'=>'i', 'й'=>'j', 'к'=>'k',
			'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r',
			'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'ts',
			'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch', 'ь'=>'', 'ы'=>'y', 'ъ'=>'',
			'э'=>'e', 'ю'=>'yu', 'я'=>'ya', 'і'=>'i', 'ї'=>'i', 'є'=>'ie',
			'А'=>'A', 'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E',
			'Ё'=>'E', 'Ж'=>'Zh', 'З'=>'Z', 'И'=>'I', 'Й'=> 'J', 'К'=>'K',
			'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
			'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Х'=>'H', 'Ц' => 'Ts',
			'Ч'=>'Ch', 'Ш'=>'Sh', 'Щ'=>'Shch', 'Ь'=>'', 'Ы'=>'Y', 'Ъ'=>'',
			'Э'=>'E', 'Ю'=>'Yu', 'Я'=>'Ya', 'І'=>'I', 'Ї'=>'I', 'Є'=>'Ie');
		//замена кирилицы входящей строки на латынь
		return strtr($string, $converter);
	}
	/**
	 * Make sting like friendly url
	 * @param $str
	 * @return string
	 */
	public function staticStr2Url($str){
		$str = $this->rus2translit($str);
		$str = strtolower($str);
		$str = preg_replace('~[^-a-z0-9_\.]+~u', '_', $str);
		$str = trim($str, "_");
		return $str;
	}

	public function str2url($str){
		return self::staticStr2Url($str);
	}


	/**
	 * Create execution start timestamp
	 * @return integer
	 */
	public function getMicrotime(){
		$time = microtime();
		$time = explode(' ', $time);
		return $time[1] + $time[0];
	}

	/**
	 * Check string for JSON data-format (call static method)
	 * @param $string
	 * @return bool
	 */
	public function isJson($string){
		return self::stringIsJson($string);
	}

	/**
	 * Check string for JSON data-format
	 * @param $string
	 * @return bool
	 */
	public static function stringIsJson($string){
		return ((is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string)))))
			? true
			: false;
	}

	/**
	 * Makes main link for route
	 * @param $path
	 * @return string
	 */
	public function getNaturalPath($path){
		$path = explode('/',$path);
		$slug = '';
		foreach($path as $item){
			if( (!is_numeric($item)) && ($item != 'create') && !empty($item) ){
				$slug .= '/'.$item;
			}else{
				break;
			}
		}
		return $slug;
	}

	/**
	 * Checking if user has permissions to visit this $path
	 * @param $path
	 * @return bool|void
	 */
	public function checkAccessToPage($path){
		//Get current authenticated user data
		$admin = Auth::user();
		//Get user role
		$admin_roles = Roles::select('slug', 'access_pages')->where('slug', '=', $admin['role'])->first();
		//Check for Grant-access rules
		if($admin_roles->access_pages == 'grant_access'){
			return true;
		}

		//Get accesses list for current role (access_pages is forbidden pages list)
		$forbidden_pages = (array)json_decode($admin_roles->access_pages);
		//get path for current page
		$natural_path = $this->getNaturalPath($path);
		//Get current page data
		$current_page = AdminMenu::select('id', 'slug')->where('slug', '=', $natural_path)->first();

		//if there is no id of current page in forbidden pages list
		if(!in_array($current_page->id, array_keys($forbidden_pages))){
			return true;
		}else{
			//Check for CRUD restrictions
			$forbidden_pages = json_decode($admin_roles->access_pages);
			$current_page_id = $current_page->id;

			//convert current page path to full-slash view
			$path = (substr($path, 0,1) != '/')? '/'.$path: $path;
			//create path to links array
			$path_array = array_values(array_diff(explode('/',$path), ['']));
			//check for action-path
			switch($path_array[count($path_array) -1]){
				case 'edit':
				case 'update':
					if(strpos($forbidden_pages->$current_page_id, 'u') !== false){
						return abort(503);
					}
				break;
				case 'create':
				case 'store':
					if(strpos($forbidden_pages->$current_page_id, 'c') !== false){
						return abort(503);
					}
				break;
				default:
					if(strpos($forbidden_pages->$current_page_id, 'r') !== false){
						return abort(503);
					}
			}
			return true;
		}
	}


	/**
	 * Function create pagination settings
	 * @param $query paginated data-collection
	 * @param $sorting_settings $_GET data of page sorting
	 * @return array
	 */
	public function createPaginationOptions($query, $sorting_settings){
		return [
			'next_page'		=> $query->nextPageUrl().'&sort_by='.$sorting_settings['sort'].'&dir='.$sorting_settings['dir'],
			'current_page'	=> $query->currentPage(),
			'last_page'		=> $query->lastPage(),
			'sort_by'		=> $sorting_settings['sort'],
			'dir'			=> $sorting_settings['dir']
		];
	}

	/**
	 * Save image file on server.
	 * Use it on form (or js FormData object) response
	 * @param $img_url
	 * @param bool $use_img_check
	 * @return string
	 */
	public function createImg($img_url, $use_img_check = true){
		if(('undefined' != $img_url) && (!empty($img_url))){
			$destinationPath = base_path() . '/public/img/';//Image storage folder
			$img_file = pathinfo($this->str2url($img_url->getClientOriginalName()));//get real file-name
			$img_file['extension'] = strtolower($img_file['extension']);//get file extension
			//check file extension
			if ($use_img_check) {
				if (
					($img_file['extension'] != 'png') &&
					($img_file['extension'] != 'jpg') &&
					($img_file['extension'] != 'jpeg') &&
					($img_file['extension'] != 'gif') &&
					($img_file['extension'] != 'svg') &&
					($img_file['extension'] != 'bmp')
				) {
					return '';
				}
			}
			//create new file-name
			$img_file = $img_file['filename'] . '_' . substr(uniqid(), 6) . '.' . $img_file['extension'];
			//save file on web-server
			$img_url->move($destinationPath, $img_file);
			$img_file = '/img/' . $img_file;
		}else{
			$img_file = '';
		}
		return $img_file;
	}

	/**
	 * Save image file on server.
	 * Use it if response has only base64 encoded data
	 * @param $img
	 * @param bool $use_img_check
	 * @return string
	 */
	public function createImgBase64($img, $use_img_check = true){
		return self::createImgBase64Static($img, $use_img_check);
	}
	public static function createImgBase64Static($img, $use_img_check = true){
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
			$filename = '/img/' . uniqid() . '.' . $extension[1];
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

	public function makeRectangleImage($filename){
		if(!empty($filename)){
			$img_resolution = getimagesize(base_path().'/public'.$filename);

			$n = $img_resolution[0] / $img_resolution[1];
			if($n <= 3){
				//Cut
				$cut_height = $img_resolution[1] - round($img_resolution[0] / 3);
				$image = Image::make(base_path().'/public'.$filename);
				$image->rotate(180);
				$image->crop($img_resolution[0], $cut_height,0, $img_resolution[1]-$cut_height);
				$image->rotate(180);
				$image->save(base_path().'/public'.$filename);
			}
		}
		return $filename;
	}


	/**
	 * @param string $folder
	 * @param $all_files
	 * @return array
	 */
	protected static function getFolders($folder = 'img', $all_files = []){
		$fp = opendir($folder);
		while($cv_file = readdir($fp)){
			if(is_file($folder . "/" . $cv_file)){
				$all_files[] = $folder . "/" . $cv_file;
			}else if(($cv_file != '.') && ($cv_file != '..') && (is_dir($folder.'/'.$cv_file))){
				self::getFolders($folder . "/" . $cv_file, $all_files);
			}
		}
		closedir($fp);
		return $all_files;
	}

	/**
	 * Function builds admin top navigation menu
	 * @param string $current_page
	 * @param int $refer_to
	 * @return string
	 */
	public static function topMenu($current_page, $refer_to = 0){
		$result = '';
		$menu = AdminMenu::select('id','title','slug','img')
			->where('refer_to','=',$refer_to)
			->orderBy('position','asc')
			->get();
		if(!empty($menu->all())){
			$result .= '<ul class="cfix">';

			foreach($menu as $item){
				//if current page is equal to menu link -> add class 'active'
				$link_active = ($current_page == $item->slug)? 'class="active"': '';

				$result .= '<li><a '.$link_active.' href="'.asset($item->slug).'">';
				//if menu item has image
				$result .= (!empty($item->img))
					? '<span class="menu-img fa '.$item->img.'"><strong>'.$item->title.'</strong></span>'
					: $item->title;
				$result .= '</a>';
				//check for inner menu elements
				$inner_count = AdminMenu::select('id')->where('refer_to','=',$item->id)->count();
				if($inner_count > 0){
					$result .= self::topMenu($current_page, $item->id);
				}
				$result .= '</li>';
			}

			$result .= '</ul>';
		}
		return $result;
	}

	/**
	 * Function builds breadcrumbs
	 * @param $page
	 * @param $type database table
	 * @return string
	 */
	public function breadcrumbs($page, $type){
		$result = [];
		$page = explode('/', $page.'/');
		$page = array_values(array_diff($page, ['']));//drop empty elements

		for($i = 0; $i < count($page); $i++){
			if(is_numeric($page[$i])){
				switch($type){
					case 'users': $select = 'email'; break;
					case 'comments': $select = 'id'; break;
					default: $select = 'title';
				}
				$link = \DB::table($type)->select($select)->find($page[$i]);
				$result[] = [
					'is_link'	=> false,
					'title'		=> $link->$select
				];
			}else{
				switch($page[$i]){
					case 'create':
						$result[] = [
							'is_link'	=> false,
							'title'		=> 'Добавление'
						];
					break;
					case 'edit': break;
					default:
						$slug = '';
						for($j = 0; $j <= $i; $j++){
							if(!is_numeric($page[$j])){
								$slug .= '/'.$page[$j];
							}
						}
						$link = AdminMenu::select('title')->where('slug', '=', $slug)->first();
						$result[] = ($i < (count($page) -1))
							? ['is_link'=> true, 'link' => $slug, 'title' => $link->title]
							: ['is_link'=> false, 'title' => $link->title];
				}
			}
		}
		return $result;
	}

	public function categoriesReferals($category_type = 0, $refer_to = 0, $result = []){
		$items = Category::select('id','title');
		if($category_type != 0){
			$items = $items->where('category_type','=',$category_type);
		}
		$items = $items->where('refer_to','=',$refer_to)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();

		if(!empty($items->all())){
			foreach($items as $item){
				$result[] = [
					'id' => $item->id,
					'title' => $item->title
				];
				if(Category::select('id')->where('refer_to','=',$refer_to)->count() > 0){
					$result = $this->categoriesReferals($category_type, $item->id, $result);
				}
			}
		}
		return $result;
	}

	/**
	 * Function build categories link list
	 * @param int $category_type
	 * @param int $refer_to
	 * @return string
	 */
	public function categoriesLinks($category_type = 0, $refer_to = 0){
		$result = '';
		$items = Category::select('id','title');
		if($category_type != 0){
			$items = $items->where('category_type','=',$category_type);
		}
		$items = $items->where('refer_to','=',$refer_to)
			->where('enabled','=',1)
			->orderBy('position','asc')
			->get();
		if(!empty($items->all())) {
			$result = '<ul>';
			foreach($items as $item){
				$result .= '<li><a href="'.route('admin.category.edit', $item->id).'">'.$item->title.'</a>';
				if(Category::select('id')->where('refer_to','=',$refer_to)->count() > 0){
					$result .= $this->categoriesLinks($category_type, $item->id);
				}
				$result .= '</li>';
			}
			$result .= '</ul>';
		}
		return $result;
	}

	/**
	 * Function build categories functional list
	 * @param $build_controls
	 * @param int $category_type
	 * @param int $refer_to
	 * @return string
	 */
	public static function buildCategoriesList($build_controls, $category_type = 0, $refer_to = 0){
		$result = '';
		$items = Category::select('id','title','slug','img_url','enabled','created_at','updated_at');
		if($category_type != 0){
			$items = $items->where('category_type','=',$category_type);
		}
		$items = $items->where('refer_to','=',$refer_to)
			->orderBy('position','asc')
			->get();
		if(!empty($items->all())){
			$result = '<ul>';
			foreach($items as $item){
				$enabled = [
					'class'			=> ($item->enabled == 1)? '': ' disabled',
					'triggerClass'	=> ($item->enabled == 1)? 'fa-check': 'fa-ban',
				];

				$img_url = '';
				if(!empty($item->img_url) && (self::stringIsJson($item->img_url))){
					$img = json_decode($item->img_url);
					$img_url = '<img src="'.$img->src.'" alt="'.$img->alt.'">';
				}

				$result .= '<li data-id="'.$item->id.'">
					<div class="category-wrap'.$enabled['class'].'">
						<div class="category-title">';
				if($build_controls){
					$result .= '
							<div class="sort-controls">
								<div class="urdl-wrap">
									<div data-direction class="item"></div>
									<div data-direction class="item fa fa-angle-up"></div>
									<div data-direction class="item"></div>
								</div>
								<div class="urdl-wrap">
									<div data-direction class="item fa fa-angle-left"></div>
									<div class="item"></div>
									<div data-direction class="item fa fa-angle-right"></div>
								</div>
								<div class="urdl-wrap">
									<div data-direction class="item"></div>
									<div data-direction class="item fa fa-angle-down"></div>
									<div data-direction class="item"></div>
								</div>
							</div>';
				}
				$result .= '
							<div class="title-wrap" title="title">'.$item->title.'</div>
						</div>
						<div class="category-slug" title="link">'.$item->slug.'</div>
						<div class="category-image">'.$img_url.'</div>
						<div class="timestamps">
							<p>Создан: '.$item->created_at.'</p>
							<p>Изменен: '.$item->updated_at.'</p>
						</div>';
				if($build_controls){
					$result .= '
						<div class="category-controls">
							<a class="fa '.$enabled['triggerClass'].'" href="#" title="enabled"></a>
							<a class="edit fa fa-pencil-square-o" href="'.route('admin.category.edit',$item->id).'" title="Edit"></a>
							<a class="drop fa fa-times" href="#" title="drop"></a>
						</div>';
				}
				$result .= '</div>';
				if(Category::select('id')->where('refer_to','=',$refer_to)->count() > 0){
					$result .= self::buildCategoriesList($build_controls, $category_type, $item->id);
				}
				$result .= '</li>';
			}
			$result .= '</ul>';
		}
		return $result;
	}

	/**
	 * Function returns filename from its path
	 * @param string $file filename with slashes
	 * @return string filename
	 */
	public function getFileName($file){
		$file = explode('/',$file);
		$file = $file[count($file) -1];
		return $file;
	}

	/**
	 * Function gets filesize and convert it to Byte-KB-MB-GB view
	 * @param $file
	 * @return string
	 */
	public static function niceFilesize($file){
		$size = filesize($file);
		$ext = 'Bytes';
		if($size > 1024){
			$size = $size/1024;
			$ext = 'KB';
		}
		if($size > 1024){
			$size = $size/1024;
			$ext = 'MB';
		}
		if($size > 1024){
			$size = $size/1024;
			$ext = 'GB';
		}
		return number_format($size, 2, '.', ' ').' '.$ext;
	}
}
