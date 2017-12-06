<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\MealDish;
use App\MealMenu;

use App\Http\Controllers\AppController;
use App\Settings;
use Illuminate\Http\Request;

class MealDishController extends AppController
{
	/**
	 * GET|HEAD /admin/restaurant/menu/dish
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if ($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'meal_dishes');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort' => 'title', 'dir' => 'asc'];

			if (isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}

			//Get dishes from DB and paginate 'em
			$dishes = MealDish::select(
				'id','title','img_url','category_id','price','ingredients',
				'is_recommended','enabled','views','created_at','updated_at'
			)->orderBy($sorting_settings['sort'], $sorting_settings['dir']);

			//run search request
			if (isset($request_data['search']) && !empty(trim($request_data['search']))) {
				$search = explode(' ', $request_data['search']);
				foreach ($search as $word) {
					$dishes = $dishes->where('id', 'LIKE', '%'.$word.'%')
						->orWhere('title', 'LIKE', '%'.$word.'%')
						->orWhere('price', 'LIKE', '%'.$word.'%')
						->orWhere('ingredients', 'LIKE', '%'.$word.'%');
				}
			}
			$dishes = $dishes->paginate(20);

			$content = [];
			foreach($dishes as $dish){
				//Get menu list where isset this dish
				$menus_list = [];
				$menus = MealMenu::select('id','title','dishes','restaurant_id')->where('dishes','LIKE','%'.$dish->id.'%')->get();
				foreach($menus as $menu){
					$menu_dishes = json_decode($menu->dishes);
					if(in_array($dish->id, $menu_dishes)){
						$restaurant = $menu->restaurant()->select('id','title')->first();
						$menus_list[] = [
							'id'		=> $menu->id,
							'title'		=> $menu->title,
							'restaurant'=> $restaurant->toArray()
						];
					}
				}
				//Get dish category
				$category = $dish->category()->select('id','title')->first();
				//Get dish preview image
				$image = ($this->isJson($dish->img_url))? json_decode($dish->img_url): null;
				$content[] = [
					'id'			=> $dish->id,
					'title'			=> $dish->title,
					'img_url'		=> (!empty($image))? $image[0]: null,
					'category'		=> (!empty($category))? $category->toArray(): null,
					'price'			=> $dish->price,
					'ingredients'	=> str_limit($dish->ingredients, 63),
					'menus'			=> $menus_list,
					'is_recommended'=> $dish->is_recommended,
					'enabled'		=> $dish->enabled,
					'views'			=> $dish->views,
					'created'		=> date('Y /m /d H:i', strtotime($dish->created_at)),
					'updated'		=> date('Y /m /d H:i', strtotime($dish->updated_at))
				];
			}

			$pagination_options = $this->createPaginationOptions($dishes, $sorting_settings);

			return view('admin.dish', [
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
	 * GET|HEAD /admin/restaurant/menu/dish/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'meal_dishes');

			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','dish')->first();

			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.dish', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'categories'	=> $categories,
				'title'			=> 'Добавление '.$page->title,
				'allow_categories'=> $settings->category_type_id
			]);
		}
	}


	/**
	 * GET|HEAD /admin/restaurant/menu/dish/{id}/edit
	 * @param $id MealDish ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'meal_dishes');

			//Get editable dish content
			$content = MealDish::select(
				'id','title','category_id','img_url','model_3d','price','dish_weight','calories','ingredients',
				'cooking_time','is_recommended','enabled'
			)->find($id);

			//Create images array
			$content->img_url = json_decode($content->img_url);
			$images = [];
			foreach($content->img_url as $image){
				$name = $this->getFileName($image->src);
				$images[] = [
					'src'		=> $image->src,
					'alt'		=> (isset($image->alt))? $image->alt: '',
					'name'		=> $name,
					'size'		=> self::niceFilesize(base_path().'/public'.$image->src)
				];
			}
			$content->img_url = $images;
			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','dish')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.dish', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'categories'	=> $categories,
				'title'			=> 'Редактирование '.$page->title.' "'.$content->title.'"',
				'content'		=> $content,
				'allow_categories'=> $settings->category_type_id
			]);
		}
	}


	/**
	 * GET|HEAD /admin/restaurant/menu/dish/{id}
	 * @param $id MealDish ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'meal_dishes');

			//Get editable dish content
			$content = MealDish::select(
				'title','category_id','img_url','model_3d','price','dish_weight','calories','ingredients',
				'cooking_time','is_recommended','enabled'
			)->find($id);

			//Create images array
			$content->img_url = json_decode($content->img_url);
			$images = [];
			foreach($content->img_url as $image){
				$name = $this->getFileName($image->src);
				$images[] = [
					'src'		=> $image->src,
					'alt'		=> (isset($image->alt))? $image->alt: '',
					'name'		=> $name,
					'size'		=> self::niceFilesize(base_path().'/public'.$image->src)
				];
			}
			$content->img_url = $images;
			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','dish')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.dish', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'categories'	=> $categories,
				'title'			=> 'Просмотр '.$page->title.' "'.$content->title.'"',
				'content'		=> $content,
				'allow_categories'=> $settings->category_type_id
			]);
		}
	}


	/**
	 * POST /admin/restaurant/menu/dish/
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['img_url'];

		//If there are dishes with such link
		$data['slug'] = (MealDish::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = MealDish::create([
			'title'			=> $data['title'],
			'slug'			=> $data['slug'],
			'category_id'	=> (isset($data['category_id']))? $data['category_id']: 0,
			'img_url'		=> json_encode($img_url),
			'model_3d'		=> $data['model_3d'],
			'price'			=> str_replace(',','.',$data['price']),
			'dish_weight'	=> (float)str_replace(',','.',$data['dish_weight']),
			'calories'		=> (!empty($data['calories']))? str_replace(',','.',$data['calories']): null,
			'cooking_time'	=> $data['cooking_time'],
			'ingredients'	=> $data['ingredients'],
			'is_recommended'=> $data['is_recommended'],
			'enabled'		=> $data['enabled'],
		]);
		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.dish.index');
		}
	}


	/**
	 * PUT|PATCH /admin/restaurant/menu/dish/{id}
	 * @param $id MealDish ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['img_url'];

		//If there are dishes with such link
		$data['slug'] = (MealDish::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = MealDish::find($id);
		$result->title			= $data['title'];
		$result->slug			= $data['slug'];
		$result->category_id	= (isset($data['category_id']))? $data['category_id']: 0;
		$result->img_url		= json_encode($img_url);
		$result->model_3d		= $data['model_3d'];
		$result->price			= str_replace(',','.',$data['price']);
		$result->dish_weight	= (float)str_replace(',','.',$data['dish_weight']);
		$result->calories		= (!empty($data['calories']))? str_replace(',','.',$data['calories']): null;
		$result->cooking_time	= $data['cooking_time'];
		$result->ingredients	= $data['ingredients'];
		$result->is_recommended	= $data['is_recommended'];
		$result->enabled		= $data['enabled'];
		$result->save();

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.dish.index');
		}
	}


	/**
	 * DELETE /admin/restaurant/menu/dish/{id}
	 * @param $id MealDish ID
	 * @return json string
	 */
	public function destroy($id){
		$result = MealDish::find($id)->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * Create 3D model file
	 * @param \Illuminate\Http\Request $request
	 * @return json string
	 */
	public function create_model_file(Request $request){
		$data = $request->all();
		$file = $this->createImg($data['file'], false, 'models_3d');
		return (!empty($file))
			?	json_encode([
					'message'	=> 'success',
					'text'		=> $file.' был успешно сохранен',
					'file'		=> $file
				])
			:	json_encode([
					'message'	=> 'error',
					'text'		=> 'Что-то пошло не так',
			]);
	}


	/**
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function processData($request){
		$data = $request->all();
		//Create slug for dish
		$data['slug'] = str_slug($this->str2url(trim($data['title'])));

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
		//Create enabled flag
		if(isset($data['ajax'])){
			$data['enabled'] = (isset($data['enabled']))? $data['enabled']: 0;
		}else{
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on'))? 1: 0;
		}
		//Create recommended flag
		if(isset($data['ajax'])){
			$data['is_recommended'] = (isset($data['is_recommended']))? $data['is_recommended']: 0;
		}else{
			$data['is_recommended'] = (isset($data['is_recommended']) && ($data['is_recommended'] == 'on'))? 1: 0;
		}

		return [
			'data'		=> $data,
			'img_url'	=> $img_url
		];
	}
}