<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\MealDish;
use App\MealMenu;
use App\Restaurant;
use App\Settings;

use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class MealMenuController extends AppController
{
	/**
	 * GET|HEAD /admin/restaurant/menu
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if ($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'meal_menus');

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
			$menus = MealMenu::orderBy($sorting_settings['sort'], $sorting_settings['dir']);

			//run search request
			if (isset($request_data['search']) && !empty(trim($request_data['search']))) {
				$search = explode(' ', $request_data['search']);
				foreach ($search as $word) {
					$menus = $menus->where('id', 'LIKE', '%'.$word.'%')
						->orWhere('title', 'LIKE', '%'.$word.'%');
				}
			}
			$menus = $menus->paginate(20);

			$content = [];
			foreach($menus as $menu){
				//Get menu dishes
				$dishes = ($this->isJson($menu->dishes))? json_decode($menu->dishes): [];
				$dishes_list = [];
				foreach($dishes as $dish_id){
					$dish = MealDish::select('title')->find($dish_id);
					if(!empty($dish)){
						$dishes_list[$dish_id] = $dish->title;
					}
				}

				$restaurant = $menu->restaurant()->select('id','title')->first();

				$content[] = [
					'id'		=> $menu->id,
					'title'		=> $menu->title,
					'restaurant'=> (!empty($restaurant))? [$restaurant->id => $restaurant->title]: null,
					'dishes'	=> $dishes_list,
					'enabled'	=> $menu->enabled,
					'created'	=> date('Y /m /d H:i', strtotime($menu->created_at)),
					'updated'	=> date('Y /m /d H:i', strtotime($menu->updated_at))
				];
			}

			$pagination_options = $this->createPaginationOptions($menus, $sorting_settings);

			return view('admin.meal_menu', [
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
	 * GET|HEAD /admin/restaurant/menu/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'meal_menus');

			//Get restaurant list
			$restaurants = Restaurant::select('id','title')->where('enabled','=',1)->orderBy('title','asc')->get();

			//Get dish list
			$dishes = MealDish::select('id','title','category_id','price')
				->where('enabled','=',1)
				->orderBy('category_id','desc')
				->get();
			$dish_list = [];
			foreach($dishes as $dish){
				//Get category
				$category = $dish->category()->select('id','title')->first();

				$dish_list[$category->id]['caption'] = (!empty($category))? $category->title: 'Категория не указана';
				$dish_list[$category->id]['items'][] = [
					'id'		=> $dish->id,
					'title'		=> $dish->title,
					'price'		=> number_format((float)$dish->price, 2, '.', ' ')
				];
			}

			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','menu')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.meal_menu', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'dishes'		=> $dish_list,
				'restaurants'	=> $restaurants,
				'title'			=> 'Добавление '.$page->title,
				'categories'	=> $categories,
				'allow_categories'=> $settings->category_type_id
			]);
		}
	}


	/**
	 * GET|HEAD /admin/restaurant/menu/{id}/edit
	 * @param $id MealMenu ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'meal_menus');

			//Get editable meal menu
			$content = MealMenu::select('id','title','restaurant_id','dishes','enabled','category_id')->find($id);
			if(empty($content)){
				return abort(404);
			}
			$content->dishes = json_decode($content->dishes);

			$temp = $this->getMealMenuAccessors();
			//Active restaurant list
			$restaurants = $temp['restaurants'];
			//Inner dishes list
			$dish_list = $temp['dish_list'];

			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','menu')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.meal_menu', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'dishes'		=> $dish_list,
				'restaurants'	=> $restaurants,
				'title'			=> 'Редактирование меню '.$page->title,
				'content'		=> $content,
				'categories'	=> $categories,
				'allow_categories'=> $settings->category_type_id
			]);
		}
	}


	/**
	 * GET|HEAD /admin/restaurant/menu/{id}
	 * @param $id MealMenu ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'meal_menus');

			//Get editable meal menu
			$content = MealMenu::select('title','restaurant_id','dishes','enabled','category_id')->find($id);
			if(empty($content)){
				return abort(404);
			}
			$content->dishes = json_decode($content->dishes);

			$temp = $this->getMealMenuAccessors();
			//Active restaurant list
			$restaurants = $temp['restaurants'];
			//Inner dishes list
			$dish_list = $temp['dish_list'];

			//Get categories settings
			$settings = Settings::select('category_type_id')->where('type','=','menu')->first();
			//Get available categories
			$categories = Category::select('id','title')->where('category_type','=',$settings->category_type_id)->get();

			return view('admin.add.meal_menu', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'dishes'		=> $dish_list,
				'restaurants'	=> $restaurants,
				'title'			=> 'Редактирование меню '.$page->title,
				'content'		=> $content,
				'categories'	=> $categories,
				'allow_categories'=> $settings->category_type_id
			]);
		}
	}


	/**
	 * POST /admin/restaurant/menu/
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$data = $this->processData($request);
		//If there are menus with such link
		$data['slug'] = (MealMenu::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = MealMenu::create([
			'title'		=> $data['title'],
			'slug'		=> $data['slug'],
			'restaurant_id'=> $data['restaurant_id'],
			'dishes'	=> $data['dish_id'],
			'enabled'	=> $data['enabled'],
			'category_id'=> (isset($data['category_id']))? $data['category_id']: 0
		]);
		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.menu.index');
		}
	}


	/**
	 * PUT|PATCH /admin/restaurant/menu/{id}
	 * @param $id MealMenu ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$data = $this->processData($request);
		//If there are menus with such link
		$data['slug'] = (MealMenu::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = MealMenu::find($id);
		$result->title		= $data['title'];
		$result->slug		= $data['slug'];
		$result->restaurant_id= $data['restaurant_id'];
		$result->dishes		= $data['dish_id'];
		$result->enabled	= $data['enabled'];
		$result->category_id= (isset($data['category_id']))? $data['category_id']: 0;
		$result->save();

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.menu.index');
		}
	}


	/**
	 * @param $id MealMenu ID
	 * @return json string
	 */
	public function destroy($id){
		$result = MealMenu::find($id)->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * Function returns active restaurant list and used in meal dishes
	 * @return array
	 */
	public function getMealMenuAccessors(){
		//Get restaurant list
		$restaurants = Restaurant::select('id','title')->where('enabled','=',1)->orderBy('title','asc')->get();

		//Get dish list
		$dishes = MealDish::select('id','title','category_id','price')
			->where('enabled','=',1)
			->orderBy('category_id','desc')
			->get();
		$dish_list = [];
		foreach($dishes as $dish){
			//Get category
			$category = $dish->category()->select('id','title')->first();

			$dish_list[$category->id]['caption'] = (!empty($category))? $category->title: 'Категория не указана';
			$dish_list[$category->id]['items'][] = [
				'id'		=> $dish->id,
				'title'		=> $dish->title,
				'price'		=> number_format((float)$dish->price, 2, '.', ' ')
			];
		}

		return [
			'restaurants' => $restaurants,
			'dish_list' => $dish_list
		];
	}

	/**
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	public function processData($request){
		$data = $request->all();
		$data['slug'] = str_slug($this->str2url(trim($data['title'])));
		//Create enabled flag
		if(isset($data['ajax'])){
			$data['enabled'] = (isset($data['enabled']))? $data['enabled']: 0;
		}else{
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on'))? 1: 0;
		}
		//Create inner dishes
		if(!isset($data['ajax'])){
			$data['dish_id'] = (isset($data['dish_id']))? json_encode($data['dish_id']): '[]';
		}
		return $data;
	}
}