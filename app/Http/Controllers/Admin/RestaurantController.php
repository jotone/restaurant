<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\MealMenu;
use App\Restaurant;
use App\Settings;

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
			$restaurants = Restaurant::select('id','title','slug','logo_img','address','rating','views','created_at', 'updated_at')
				->orderBy($sorting_settings['sort'], $sorting_settings['dir']);

			//run search request
			if (isset($request_data['search']) && !empty(trim($request_data['search']))) {
				$search = explode(' ', $request_data['search']);
				foreach ($search as $word) {
					$restaurants = $restaurants->where('id', 'LIKE', '%' . $word . '%')
						->orWhere('title', 'LIKE', '%' . $word . '%')
						->orWhere('address', 'LIKE', '%' . $word . '%');
				}
			}
			$restaurants = $restaurants->paginate(20);

			$content = [];
			foreach($restaurants as $restaurant){
				$menus = $restaurant->mealMenus()->select('id','title')->get();
				$content[] = [
					'id'		=> $restaurant->id,
					'title'		=> $restaurant->title,
					'slug'		=> $restaurant->slug,
					'logo'		=> ($this->isJson($restaurant->logo_img))? json_decode($restaurant->logo_img): null,
					'address'	=> str_limit($restaurant->address, 63),
					'menus'		=> $menus->toArray(),
					'rating'	=> ($this->isJson($restaurant->rating))? json_decode($restaurant->rating): null,
					'views'		=> $restaurant->views,
					'created'	=> date('Y /m /d H:i', strtotime($restaurant->created_at)),
					'updated'	=> date('Y /m /d H:i', strtotime($restaurant->updated_at))
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

			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','restaurant')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.restaurant', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Добавление ресторана',
				'menus'			=> $menus,
				'categories'	=> $categories,
				'allow_categories'=> $settings->category_type_id
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
				'id','title','logo_img','img_url','text','address','work_time','has_delivery','has_wifi',
				'etc_data','rating','enabled','category_id'
			)->find($id);
			if(empty($content)){
				return abort(404);
			}

			$content = $this->getRestaurantContent($content);

			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','restaurant')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.restaurant', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование ресторана "'.$content->title.'"',
				'menus'			=> $menus,
				'content'		=> $content,
				'categories'	=> $categories,
				'allow_categories'=> $settings->category_type_id
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
				'id','title','logo_img','img_url','text','address','work_time','has_delivery','has_wifi',
				'etc_data','rating','enabled','category_id'
			)->find($id);
			if(empty($content)){
				return abort(404);
			}

			$content = $this->getRestaurantContent($content);
			unset($content->id);

			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','restaurant')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.restaurant', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование ресторана "'.$content->title.'"',
				'menus'			=> $menus,
				'content'		=> $content,
				'categories'	=> $categories,
				'allow_categories'=> $settings->category_type_id
			]);
		}
	}


	/**
	 * POST /admin/restaurant
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$temp = $this->processData($request);
		$data	= $temp['data'];
		$logo	= $temp['logo'];
		$img_url= $temp['img_url'];

		//If there are restaurants with such link
		$data['slug'] = (Restaurant::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Restaurant::create([
			'title'			=> $data['title'],
			'slug'			=> $data['slug'],
			'logo_img'		=> $logo,
			'img_url'		=> json_encode($img_url),
			'text'			=> $data['text'],
			'address'		=> $data['address'],
			'work_time'		=> json_encode([
				'begin'			=>$data['time_begin'],
				'finish'		=> $data['time_finish']
			]),
			'has_delivery'	=> $data['has_delivery'],
			'has_wifi'		=> $data['has_wifi'],
			'rating'		=> json_encode([
				'p'	=> $data['likes'],
				'n'	=> $data['dislikes']
			]),
			'enabled'		=> $data['enabled'],
			'category_id'	=> (isset($data['category_id']))? $data['category_id']: 0
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
		$temp = $this->processData($request);
		$data	= $temp['data'];
		$logo	= $temp['logo'];
		$img_url= $temp['img_url'];

		//If there are restaurants with such link
		$data['slug'] = (Restaurant::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Restaurant::find($id);
		$result->title		= $data['title'];
		$result->slug		= $data['slug'];
		$result->logo_img	= $logo;
		$result->img_url	= json_encode($img_url);
		$result->text		= $data['text'];
		$result->address	= $data['address'];
		$result->work_time	= json_encode([
			'begin'	=>$data['time_begin'],
			'finish'=> $data['time_finish']
		]);
		$result->has_delivery= $data['has_delivery'];
		$result->has_wifi	= $data['has_wifi'];
		$result->rating		= json_encode([
			'p'	=> $data['likes'],
			'n'	=> $data['dislikes']
		]);
		$result->enabled	= $data['enabled'];
		$result->category_id=  (isset($data['category_id']))? $data['category_id']: 0;
		$result->save();

		if($result != false){
			foreach($data['menus'] as $menu){
				MealMenu::where('id','=',$menu)->update(['restaurant_id'=>$result->id]);
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
		//Create rating array
		$content->rating = ($this->isJson($content->rating))
			? json_decode($content->rating)
			: (object)[
				'p'=>0,
				'n'=>0
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
		//Work time
		if(!isset($data['time_begin']) || !isset($data['time_finish'])){
			$data['time_begin'] = '00:00';
			$data['time_finish'] = '00:00';
		}
		//Create rating
		$data['likes'] = (isset($data['likes']) && !empty($data['likes']))? $data['likes']: 0;
		$data['dislikes'] = (isset($data['dislikes']) && !empty($data['dislikes']))? $data['dislikes']: 0;

		//If image was sent as $_FILE value
		if(!empty($request->file())){
			$logo = json_encode([
				'src' => $this->createImg($request->file('logo')),
				'alt' => ''
			]);
			//if image was sent as base64encoded file content
		}else if(isset($data['logo']) && $this->isJson($data['logo'])){
			$temp = json_decode($data['logo']);
			$logo = json_encode([
				'src' => ($temp->type == 'upload')
					? $this->createImgBase64($temp->src)
					: $temp->src,
				'alt' => ''
			]);
		}else{
			$logo = '';
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
			'logo'		=> $logo,
			'img_url'	=> $img_url
		];
	}
}