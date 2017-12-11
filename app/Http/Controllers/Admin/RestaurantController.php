<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\MealMenu;
use App\Restaurant;
use App\Settings;

use Auth;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class RestaurantController extends AppController
{
	/**
	 * GET|HEAD /admin/restaurant
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if ($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'restaurants');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort' => 'id', 'dir' => 'asc'];

			if (isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}

			//Get restaurants from DB and paginate 'em
			$restaurants = Restaurant::select(
				'id','title','slug','logo_img','address','rating','views','enabled',
				'created_by','updated_by','created_at','updated_at'
			)->orderBy($sorting_settings['sort'], $sorting_settings['dir']);

			//run search request
			if (isset($request_data['search']) && !empty(trim($request_data['search']))) {
				$search = explode(' ', $request_data['search']);
				foreach ($search as $word) {
					$restaurants = $restaurants->where('id', 'LIKE', '%'.$word.'%')
						->orWhere('title', 'LIKE', '%'.$word.'%')
						->orWhere('slug', 'LIKE', '%'.$word.'%')
						->orWhere('address', 'LIKE', '%'.$word.'%');
				}
			}
			$restaurants = $restaurants->paginate(20);

			$content = [];
			foreach($restaurants as $restaurant){
				$menus = $restaurant->mealMenus()->select('id','title')->get();
				//Get creator
				$created_by = $restaurant->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $restaurant->updatedBy()->select('name','email')->first();
				$content[] = [
					'id'		=> $restaurant->id,
					'title'		=> $restaurant->title,
					'slug'		=> $restaurant->slug,
					'logo'		=> ($this->isJson($restaurant->logo_img))? json_decode($restaurant->logo_img): null,
					'address'	=> str_limit($restaurant->address, 63),
					'menus'		=> $menus->toArray(),
					'rating'	=> $restaurant->rating,
					'views'		=> $restaurant->views,
					'enabled'	=> $restaurant->enabled,
					'created'	=> date('Y /m /d H:i', strtotime($restaurant->created_at)),
					'updated'	=> date('Y /m /d H:i', strtotime($restaurant->updated_at)),
					'created_by'=> (!empty($created_by))
									? ['name' => $created_by->name, 'email' => $created_by->email]
									: [],
					'updated_by'=> (!empty($updated_by))
									? ['name' => $updated_by->name, 'email' => $updated_by->email]
									: [],
				];
			}

			$pagination_options = $this->createPaginationOptions($restaurants, $sorting_settings);

			return view('admin.restaurant', [
				'start'		=> $start,
				'page'		=> $current_page,
				'breadcrumbs'=> $breadcrumbs,
				'title'		=> $page->title,
				'pagination'=> $pagination_options,
				'content'	=> $content
			]);
		}
	}


	/**
	 * GET|HEAD /admin/restaurant/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'restaurants');

			$menus = MealMenu::select('id','title')->where('enabled','=',1)->get();

			//Get restaurant settings
			$settings = Settings::select('options')->where('slug','=','restaurant')->first()->toArray();
			$settings = json_decode($settings['options']);

			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type)->get();

			return view('admin.add.restaurant', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Добавление ресторана',
				'menus'			=> $menus,
				'categories'	=> $categories,
				'settings'		=> $settings
			]);
		}
	}


	/**
	 * GET|HEAD /admin/restaurant/{id}/edit
	 * @param $id Restaurant ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'restaurants');

			$menus = MealMenu::select('id','title')->where('enabled','=',1)->get();

			$content = Restaurant::select(
				'id','title','logo_img','square_img','large_img','img_url','text','address','work_time','has_delivery','has_wifi',
				'coordinates','etc_data','rating','enabled','category_id'
			)->find($id);
			if(empty($content)){
				return abort(404);
			}

			$content = $this->getRestaurantContent($content);

			//Get restaurant settings
			$settings = Settings::select('options')->where('slug','=','restaurant')->first()->toArray();
			$settings = json_decode($settings['options']);
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type)->get();

			return view('admin.add.restaurant', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование ресторана "'.$content->title.'"',
				'menus'			=> $menus,
				'content'		=> $content,
				'categories'	=> $categories,
				'settings'		=> $settings
			]);
		}
	}


	/**
	 * GET|HEAD /admin/restaurant/{id}
	 * @param $id Restaurant ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'restaurants');

			$menus = MealMenu::select('id','title')->where('enabled','=',1)->get();

			$content = Restaurant::select(
				'id','title','logo_img','square_img','large_img','img_url','text','address','work_time','has_delivery','has_wifi',
				'coordinates','etc_data','rating','enabled','category_id'
			)->find($id);
			if(empty($content)){
				return abort(404);
			}

			$content = $this->getRestaurantContent($content);
			unset($content->id);

			//Get restaurant settings
			$settings = Settings::select('options')->where('slug','=','restaurant')->first()->toArray();
			$settings = json_decode($settings['options']);
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type)->get();

			return view('admin.add.restaurant', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование ресторана "'.$content->title.'"',
				'menus'			=> $menus,
				'content'		=> $content,
				'categories'	=> $categories,
				'settings'		=> $settings
			]);
		}
	}


	/**
	 * POST /admin/restaurant
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$user = Auth::user();

		$temp = $this->processData($request);
		$data	= $temp['data'];
		$logo	= $temp['logo_img'];
		$square	= $temp['square_img'];
		$large	= $temp['large_img'];
		$img_url= $temp['img_url'];

		//If there are restaurants with such link
		$data['slug'] = (Restaurant::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Restaurant::create([
			'title'			=> $data['title'],
			'slug'			=> $data['slug'],
			'logo_img'		=> $logo,
			'square_img'	=> $square,
			'large_img'		=> $large,
			'img_url'		=> json_encode($img_url),
			'text'			=> $data['text'],
			'address'		=> $data['address'],
			'work_time'		=> json_encode([
				'begin'			=>$data['time_begin'],
				'finish'		=> $data['time_finish']
			]),
			'coordinates'	=> json_encode([
				'x' => (isset($data['coordinateX']))? $data['coordinateX']: 0,
				'y' => (isset($data['coordinateY']))? $data['coordinateY']: 0
			]),
			'has_delivery'	=> $data['has_delivery'],
			'has_wifi'		=> $data['has_wifi'],
			'rating'		=> json_encode([
				'p'	=> $data['likes'],
				'n'	=> $data['dislikes']
			]),
			'enabled'		=> $data['enabled'],
			'category_id'	=> $data['category'],
			'created_by'		=> $user['id'],
			'updated_by'		=> $user['id']
		]);
		if($result != false){
			if(isset($data['menus'])){
				foreach($data['menus'] as $menu){
					MealMenu::where('id','=',$menu)->update(['restaurant_id'=>$result->id]);
				}
			}
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.restaurant.index');
		}
	}


	/**
	 * PUT|PATCH /admin/restaurant/{id}
	 * @param $id Restaurant ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$user = Auth::user();
		$temp = $this->processData($request);
		$data	= $temp['data'];
		$logo	= $temp['logo_img'];
		$square	= $temp['square_img'];
		$large	= $temp['large_img'];
		$img_url= $temp['img_url'];

		//If there are restaurants with such link
		$data['slug'] = (Restaurant::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Restaurant::find($id);
		$result->title			= $data['title'];
		$result->slug			= $data['slug'];
		$result->logo_img		= $logo;
		$result->square_img		= $square;
		$result->large_img		= $large;
		$result->img_url		= json_encode($img_url);
		$result->text			= $data['text'];
		$result->address		= $data['address'];
		$result->work_time		= json_encode([
			'begin'	=>$data['time_begin'],
			'finish'=> $data['time_finish']
		]);
		$result->coordinates	= json_encode([
			'x' => (isset($data['coordinateX']))? $data['coordinateX']: 0,
			'y' => (isset($data['coordinateY']))? $data['coordinateY']: 0
		]);
		$result->has_delivery	= $data['has_delivery'];
		$result->has_wifi		= $data['has_wifi'];
		$result->rating			= json_encode([
			'p'	=> $data['likes'],
			'n'	=> $data['dislikes']
		]);
		$result->enabled		= $data['enabled'];
		$result->category_id	= $data['category'];
		$result->updated_by		= $user['id'];
		$result->save();

		if($result != false){
			if(isset($data['menus'])){
				foreach($data['menus'] as $menu){
					MealMenu::where('id','=',$menu)->update(['restaurant_id'=>$result->id]);
				}
			}else{
				MealMenu::where('restaurant_id','=',$result->id)->update(['restaurant_id'=>0]);
			}

			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.restaurant.index');
		}
	}


	/**
	 * DELETE /admin/restaurant/{id}
	 * @param $id Restaurant ID
	 * @return json string
	 */
	public function destroy($id){
		MealMenu::where('restaurant_id','=',$id)->update([
			'restaurant_id' => 0
		]);
		$result = Restaurant::find($id)->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * Parse restaurant content
	 * @param \App\Restaurant $content
	 * @return \App\Restaurant $content
	 */
	public function getRestaurantContent($content){
		//Create logo image
		$content->logo_img = ($this->isJson($content->logo_img))? json_decode($content->logo_img): [];
		$content->square_img = ($this->isJson($content->square_img))? json_decode($content->square_img): [];
		$content->large_img = ($this->isJson($content->large_img))? json_decode($content->large_img): [];
		//Create images array
		$content->img_url = json_decode($content->img_url);
		$images = [];
		if(!empty($content->img_url)){
			foreach($content->img_url as $image){
				$name = $this->getFileName($image->src);
				$images[] = [
					'src'		=> $image->src,
					'alt'		=> (isset($image->alt))? $image->alt: '',
					'name'		=> $name,
					'size'		=> self::niceFilesize(base_path().'/public'.$image->src)
				];
			}
		}
		$content->img_url = $images;
		//Create work time array
		$content->work_time = ($this->isJson($content->work_time))
			? json_decode($content->work_time)
			: (object)[
				'begin'=>'00:00',
				'finish'=> '00:00'
			];
		//Get restaurant menus
		$menus = MealMenu::select('id')->where('restaurant_id','=',$content->id)->get();
		$menu_list = [];
		foreach ($menus as $menu){
			$menu_list[] = $menu->id;
		}
		$content->menus = $menu_list;
		return $content;
	}


	/**
	 * Transform restaurant data into proper view
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function processData($request){
		$data = $request->all();

		//Create slug
		$data['slug'] = str_slug($this->str2url(trim($data['title'])));
		if(isset($data['ajax'])){
			//Create enabled flag
			$data['enabled'] = (isset($data['enabled']))? $data['enabled']: 0;
			//Create delivery flag
			$data['has_delivery'] = (isset($data['has_delivery']))? $data['has_delivery']: 0;
			//Create wi-fi flag
			$data['has_wifi'] = (isset($data['has_wifi']))? $data['has_wifi']: 0;
		}else{
			//Create enabled flag
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on'))? 1: 0;
			//Create delivery flag
			$data['has_delivery'] = (isset($data['has_delivery']) && ($data['has_delivery'] == 'on'))? 1: 0;
			//Create wi-fi flag
			$data['has_wifi'] = (isset($data['has_wifi']) && ($data['has_wifi'] == 'on'))? 1: 0;
		}

		//Create Category
		if(isset($data['category'])){
			$data['category'] =(is_array($data['category']))
				? json_encode($data['category'])
				: '["'.$data['category'].'"]';
		}else{
			$data['category'] = '["0"]';
		}

		//Work time
		if(!isset($data['time_begin']) || !isset($data['time_finish'])){
			$data['time_begin'] = '00:00';
			$data['time_finish'] = '00:00';
		}

		//Create rating
		$data['likes'] = (isset($data['likes']) && !empty($data['likes']))? $data['likes']: 0;
		$data['dislikes'] = (isset($data['dislikes']) && !empty($data['dislikes']))? $data['dislikes']: 0;

		//Logo image
		//If image was sent as $_FILE value
		if(!empty($request->file())){
			$img = $this->createImg($request->file('logo_img'));
			$size = getimagesize(base_path().'/public'.$img);

			$logo_img = json_encode([
				'src'	=> $img,
				'width'	=> $size[0],
				'height'=> $size[1]
			]);
			//if image was sent as base64encoded file content
		}else if(isset($data['logo_img']) && $this->isJson($data['logo_img'])){
			$data['logo_img'] = json_decode($data['logo_img']);

			$img = ($data['logo_img']->type == 'upload')
				? $this->createImgBase64($data['logo_img']->src)
				: $data['logo_img']->src;

			$size = (!empty($data['logo_img']->src))
				? getimagesize(base_path().'/public'.$img)
				: [0,0];

			$logo_img = json_encode([
				'src'	=> $img,
				'width'	=> $size[0],
				'height'=> $size[1]
			]);
		}else{
			$logo_img = '';
		}

		//Square image
		if(!empty($request->file())){
			$img = $this->createImg($request->file('logo_img'));
			$size = getimagesize(base_path().'/public'.$img);

			$square_img = json_encode([
				'src'	=> $img,
				'width'	=> $size[0],
				'height'=> $size[1]
			]);
			//if image was sent as base64encoded file content
		}else if(isset($data['square_img']) && $this->isJson($data['square_img'])){
			$data['square_img'] = json_decode($data['square_img']);

			$img = ($data['square_img']->type == 'upload')
				? $this->createImgBase64($data['square_img']->src)
				: $data['square_img']->src;

			$size = (!empty($data['square_img']->src))
				? getimagesize(base_path().'/public'.$img)
				: [0,0];

			$square_img = json_encode([
				'src'	=> $img,
				'width'	=> $size[0],
				'height'=> $size[1]
			]);
		}else{
			$square_img = '';
		}

		//large Image
		if(!empty($request->file())){
			$img =  $this->makeRectangleImage($this->createImg($request->file('large_img')));
			$size = getimagesize(base_path().'/public'.$img);

			$large_img = json_encode([
				'src'	=> $img,
				'width'	=> $size[0],
				'height'=> $size[1]
			]);
			//if image was sent as base64encoded file content
		}else if(isset($data['large_img']) && $this->isJson($data['large_img'])){
			$data['large_img'] = json_decode($data['large_img']);

			$img = ($data['large_img']->type == 'upload')
				? $this->makeRectangleImage($this->createImgBase64($data['large_img']->src))
				: $this->makeRectangleImage($data['large_img']->src);

			$size = (!empty($data['large_img']->src))
				? getimagesize(base_path().'/public'.$img)
				: [0,0];

			$large_img = json_encode([
				'src'	=> $img,
				'width'	=> $size[0],
				'height'=> $size[1]
			]);
		}else{
			$large_img = '';
		}

		//Create images array
		$img_url = [];
		//If image data was sent by ajax
		if(isset($data['ajax']) && isset($data['images'])){
			if($this->isJson($data['images']) || is_array($data['images'])){
				if($this->isJson($data['images'])){
					$data['images'] = json_decode($data['images']);
				}
				foreach($data['images'] as $image){
					$image = (array)$image;
					if($image['type'] == 'file'){
						$img_url[] = [
							'src' => $image['src'],
							'alt' => $image['alt']
						];
					}else if($image['type'] == 'upload'){
						$img_url[] = [
							'src' => $this->createImg($image['src']),
							'alt' => $image['alt']
						];
					}
				}
			}
		//If image data was sent by form
		}else if(!empty($request->file())){
			foreach($request->file('images') as $image){
				if($image->isValid()){
					$img_url[] = [
						'src' => $this->createImg($image),
						'alt' => ''
					];
				}
			}
		}

		return [
			'data'		=> $data,
			'logo_img'	=> $logo_img,
			'square_img'=> $square_img,
			'large_img'	=> $large_img,
			'img_url'	=> $img_url
		];
	}
}