<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\CategoryTypes;

use Auth;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class CategoriesTypesController extends AppController
{
	/**
	 * GET|HEAD /admin/category_types
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug','=',$current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'category_types');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort'=>'id', 'dir'=>'asc'];
			//Process sorting income request
			if(isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}
			//Get categories types by income sorting request
			$category_types = CategoryTypes::orderBy($sorting_settings['sort'], $sorting_settings['dir']);
			//run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$category_types = $category_types->where('id','LIKE','%'.$word.'%')
						->orWhere('title','LIKE','%'.$word.'%')
						->orWhere('slug','LIKE','%'.$word.'%');
				}
			}
			//Make pagination
			$category_types = $category_types->paginate(20);

			$content = [];
			foreach($category_types as $category_type){
				//Get inner categories that refer to current category type
				$inner_categories = $this->categoriesLinks($category_type->id);
				//Get creator
				$created_by = $category_type->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $category_type->updatedBy()->select('name','email')->first();
				//Create category types list
				$content[] = [
					'id'		=> $category_type->id,
					'title'		=> $category_type->title,
					'slug'		=> $category_type->slug,
					'enabled'	=> $category_type->enabled,
					'categories'=> $inner_categories,
					'created'	=> date('d/ M /Y H:i', strtotime($category_type->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($category_type->updated_at)),
					'created_by'=> (!empty($created_by))
									? ['name' => $created_by->name, 'email' => $created_by->email]
									: [],
					'updated_by'=> (!empty($updated_by))
									? ['name' => $updated_by->name, 'email' => $updated_by->email]
									: [],
				];
			}

			$pagination_options = $this->createPaginationOptions($category_types, $sorting_settings);

			return view('admin.category_types', [
				'start'		=> $start,
				'page'		=> $current_page,
				'breadcrumbs'=> $breadcrumbs,
				'title'		=> 'Types Of '.$page->title,
				'pagination'=> $pagination_options,
				'content'	=> $content
			]);
		}
	}


	/**
	 * GET|HEAD /admin/category_types/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'category_types');

			//List of data-fields that should be used for inner categories
			$start_options = [
				'image'	=> 'Use image for categories',
				'text'	=> 'Use text',
				'meta'	=> 'Use description, keywords and title tags',
				'seo'	=> 'Use seo data'
			];

			return view('admin.add.category_types', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Add Type Of '.$page->title,
				'options'		=> $start_options
			]);
		}
	}


	/**
	 * GET|HEAD /admin/category_types/{category_type}/edit
	 * @param $id ID of Category Type
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'category_types');

			//take current category type
			$category_type = CategoryTypes::select(
				'id','title','slug','options','enabled','created_by','created_at','updated_by','updated_at'
			)->find($id);

			//Convert category type data into proper view
			$category_type = $this->getCategoryTypeData($category_type);

			//Get categories for this category type
			$categories = self::buildCategoriesList(true, $id);

			//List of data-fields that should be used for inner categories
			$start_options = [
				'image'	=> 'Use image for categories',
				'text'	=> 'Use text',
				'meta'	=> 'Use description, keywords and title tags',
				'seo'	=> 'Use seo data'
			];

			return view('admin.add.category_types', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Edit category type '.$page->title,
				'content'		=> $category_type,
				'categories'	=> $categories,
				'options'		=> $start_options,
			]);
		}
	}


	/**
	 * GET|HEAD /admin/category_types/{category_type}
	 * @param $id ID of Category Type
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'category_types');

			//take current category type
			$category_type = CategoryTypes::select('title','slug','options','enabled','created_by','created_at','updated_by','updated_at')
				->find($id);

			//Convert category type data into proper view
			$category_type = $this->getCategoryTypeData($category_type);

			//Get categories for this category type
			$categories = self::buildCategoriesList(false, $id);

			//List of data-fields that should be used for inner categories
			$start_options = [
				'image'	=> 'Use image for categories',
				'text'	=> 'Use text',
				'meta'	=> 'Use description, keywords and title tags',
				'seo'	=> 'Use seo data'
			];

			return view('admin.add.category_types', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'View Type Of '.$page->title,
				'content'		=> $category_type,
				'categories'	=> $categories,
				'options'		=> $start_options,
			]);
		}
	}


	/**
	 * POST /admin/category_types
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store(Request $request){
		$data = $this->processData($request);
		//Get current user
		$user = Auth::user();

		$data['slug'] = (CategoryTypes::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'] = trim($data['slug']).'_'.uniqid()
			: $data['slug'];

		$result = CategoryTypes::create([
			'title'=> trim($data['title']),
			'slug'=> str_slug(trim($data['slug'])),
			'options'=> $data['options'],
			'enabled'=> $data['enabled'],
			'created_by'=> $user['id'],
			'updated_by'=> $user['id']
		]);

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.category_types.index');
		}
	}


	/**
	 * PUT|PATCH /admin/category_types/{category_type}
	 * @param $id CategoryTypes ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id, Request $request){
		$data = $this->processData($request);
		//Get current user
		$user = Auth::user();

		//Get current category type
		$result = CategoryTypes::find($id);
		if(!empty($result)){
			if(isset($data['title'])){
				$data['slug'] = (CategoryTypes::where('id','!=', $id)->where('slug','=',$data['slug'])->count() > 0)
					? $data['slug'].'_'.uniqid()
					: $data['slug'];

				$result->title	= trim($data['title']);
				$result->slug	= str_slug(trim($data['slug']));
			}

			if(isset($data['options'])){
				$result->options	= json_encode($data['options']);
			}

			$result->enabled	= $data['enabled'];
			$result->updated_by	= $user['id'];

			//If categories changed their positions
			if(isset($data['positions']) && $this->isJson($data['positions'])){
				$positions = json_decode($data['positions']);
				foreach($positions as $position){
					$category = Category::find($position->id);
					$category->position = $position->position;
					$category->refer_to = $position->refer_to;
					$category->save();
				}
			}

			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.category_types.index');
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['There is no category type with ID #'.$id]
			]);
		}
	}


	/**
	 * DELETE /admin/category_types/{category_type}
	 * @param $id category type ID
	 * @return string
	 */
	public function destroy($id){
		Category::where('category_type','=',$id)->delete();
		$category_type = CategoryTypes::find($id)->delete();
		if($category_type != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * @param \App\CategoryTypes $category_type
	 * @return \App\CategoryTypes $category_type
	 */
	public function getCategoryTypeData($category_type){
		//Decode optons
		$category_type->options = json_decode($category_type->options);
		//Get creator
		$category_type->created_by = $category_type->createdBy()->select('name','email')->first()->toArray();
		//Get updater
		$category_type->updated_by = $category_type->updatedBy()->select('name','email')->first()->toArray();

		return $category_type;
	}


	/**
	 * @param \Illuminate\Http\Request $request
	 * @return mixed
	 */
	public function processData($request){
		$data = $request->all();

		if(isset($data['title']) && empty($data['slug'])){
			$data['slug'] = $this->str2url(trim($data['title']));
		}

		if(isset($data['ajax'])){
			$data['enabled'] = (isset($data['enabled']))? $data['enabled']: 0;

			if(isset($data['options'])){
				$data['options'] = json_encode($data['options']);
			}
		}else{
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on'))? 1: 0;

			if(isset($data['options'])){
				$data['options'] = json_encode([
					'image'	=> (isset($data['option_image']) && ($data['option_image'] == 'on'))? 1: 0,
					'text'	=> (isset($data['option_text']) && ($data['option_text'] == 'on'))? 1: 0,
					'meta'	=> (isset($data['option_meta']) && ($data['option_meta'] == 'on'))? 1: 0,
					'seo'	=> (isset($data['option_seo']) && ($data['option_seo'] == 'on'))? 1: 0,
				]);
			}
		}

		return $data;
	}
}