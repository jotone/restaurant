<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Pages;
use App\Http\Controllers\Admin\PageContentController;
use App\Template;

use Auth;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class PagesController extends AppController
{
	/**
	 * GET|HEAD /admin/pages
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'pages');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort'=>'title', 'dir'=>'asc'];
			//Process sorting income request
			if(isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}

			$content_pages = Pages::select('id','title','slug','template_id','created_by','updated_by','created_at','updated_at')
				->orderBy($sorting_settings['sort'], $sorting_settings['dir']);

			//Run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$content_pages = $content_pages->where('id','LIKE','%'.$word.'%')
						->orWhere('title','LIKE','%'.$word.'%')
						->orWhere('slug','LIKE','%'.$word.'%');
				}
			}
			//Make pagination
			$content_pages = $content_pages->paginate(20);

			$content = [];
			foreach($content_pages as $content_page){
				//Get template data
				$template = $content_page->template()->select('id','title')->first();
				//Get creator
				$created_by = $content_page->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $content_page->updatedBy()->select('name','email')->first();
				//Create pages list
				$page_data = $content_page->content()->select('type','meta_key')->get();
				$content[] = [
					'id'		=> $content_page->id,
					'title'		=> $content_page->title,
					'slug'		=> $content_page->slug,
					'template'	=> (!empty($template))? $template: [],
					'content'	=> (!empty($page_data->all()))? $page_data->toArray(): [],
					'created'	=> date('d/ M /Y H:i', strtotime($content_page->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($content_page->updated_at)),
					'created_by'=> (!empty($created_by))
								? ['name' => $created_by->name, 'email' => $created_by->email]
								: [],
					'updated_by'=> (!empty($updated_by))
								? ['name' => $updated_by->name, 'email' => $updated_by->email]
								: [],
				];
			}
			$pagination_options = $this->createPaginationOptions($content_pages, $sorting_settings);

			return view('admin.pages', [
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
	 * GET|HEAD /admin/pages/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'pages');

			//Get templates list
			$templates = Template::select('id','title')->where('enabled','=',1)->orderBy('title','asc')->get();

			$current_template = null;
			if($request->input('template')){
				$current_template = Template::select('id','html_content')->find($request->input('template'));
			}

			return view('admin.add.pages', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Добавление '.$page->title,
				'templates'		=> $templates,
				'current_template'=> $current_template
			]);
		}
	}


	/**
	 * GET|HEAD /admin/pages/{id}/edit
	 * @param $id \App\Page ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'pages');

			//Get page data
			$current_page = Pages::find($id);

			//Get page creator
			$current_page->created_by = $current_page->createdBy()->select('name','email')->first();
			//Get page editor
			$current_page->updated_by = $current_page->updatedBy()->select('name','email')->first();

			$current_template = null;
			if($current_page->template_id){
				$current_template = Template::select('id','html_content')->find($current_page->template_id);
			}

			return view('admin.add.pages', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование страницы "'.$current_page->title.'"',
				'content'		=> $current_page,
				'current_template'=> $current_template,
				'type'			=> 1
			]);
		}
	}


	/**
	 * GET|HEAD /admin/pages/{id}
	 * @param $id \App\Page ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'pages');

			//Get page data
			$current_page = Pages::find($id);

			//Get page creator
			$current_page->created_by = $current_page->createdBy()->select('name','email')->first();
			//Get page editor
			$current_page->updated_by = $current_page->updatedBy()->select('name','email')->first();

			$current_template = null;
			if($current_page->template_id){
				$current_template = Template::select('id','html_content')->find($current_page->template_id);
			}

			return view('admin.add.pages', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Просмотр страницы "'.$current_page->title.'"',
				'content'		=> $current_page,
				'current_template'=> $current_template,
				'type'			=> 0
			]);
		}
	}


	/**
	 * POST /admin/pages
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$user = Auth::user();
		$data = $this->processData($request);

		$data['slug'] = (Pages::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Pages::create([
			'title'				=> trim($data['title']),
			'slug'				=> $data['slug'],
			'meta_title'		=> $data['meta_title'],
			'meta_description'	=> $data['meta_description'],
			'meta_keywords'		=> $data['meta_keywords'],
			'need_seo'			=> $data['need_seo'],
			'seo_title'			=> $data['seo_title'],
			'seo_text'			=> $data['seo_text'],
			'template_id'		=> $data['template_id'],
			'enabled'			=> $data['enabled'],
			'created_by'		=> $user['id'],
			'updated_by'		=> $user['id']
		]);

		unset($data['_token']);
		unset($data['_method']);
		unset($data['save']);
		unset($data['id']);
		unset($data['title']);
		unset($data['slug']);
		unset($data['meta_title']);
		unset($data['meta_description']);
		unset($data['meta_keywords']);
		unset($data['need_seo']);
		unset($data['seo_title']);
		unset($data['seo_text']);
		unset($data['template_id']);
		unset($data['enabled']);

		if(isset($data['content'])){
			$content = json_decode($data['content']);
			foreach($content as $item){
				PageContentController::store($result->id, $item);
			}
		}
		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.pages.index');
		}
	}


	public function update($id, Request $request){
		$user = Auth::user();
		$data = $this->processData($request);

		$data['slug'] = (Pages::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Pages::find($id);
		if(!empty($result)){
			$result->title				= trim($data['title']);
			$result->slug				= $data['slug'];
			$result->meta_title			= $data['meta_title'];
			$result->meta_description	= $data['meta_description'];
			$result->meta_keywords		= $data['meta_keywords'];
			$result->need_seo			= $data['need_seo'];
			$result->seo_title			= $data['seo_title'];
			$result->seo_text			= $data['seo_text'];
			$result->enabled			= $data['enabled'];
			$result->updated_by			= $user['id'];
			$result->save();

			unset($data['_token']);
			unset($data['_method']);
			unset($data['save']);
			unset($data['id']);
			unset($data['title']);
			unset($data['slug']);
			unset($data['meta_title']);
			unset($data['meta_description']);
			unset($data['meta_keywords']);
			unset($data['need_seo']);
			unset($data['seo_title']);
			unset($data['seo_text']);
			unset($data['template_id']);
			unset($data['enabled']);

			if(isset($data['content'])){
				$content = json_decode($data['content']);
				foreach($content as $item){
					PageContentController::update($id, $item);
				}
			}

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.pages.index');
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['Страница с ID #'.$id.' отсутствует']
			]);
		}
	}


	public function destroy($id){
		PageContentController::destroy($id);
		$result = Pages::find($id)->delete();
		if($result != false){
			return json_encode([
				'message'=> 'success'
			]);
		}
	}


	/**
	 * @param \Illuminate\Http\Request $request
	 * @return mixed
	 */
	public function processData($request){
		$data = $request->all();
		//Create slug link
		if(empty($data['slug'])) {
			$data['slug'] = $this->str2url(trim($data['title']));
		}

		//Change checkbox values to boolean
		if(isset($data['ajax'])){
			$data['enabled'] = (isset($data['enabled']))? $data['enabled']: 0;
			$data['need_seo'] = (isset($data['need_seo']))? $data['need_seo']: 0;
		}else{
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on'))? 1: 0;
			$data['need_seo'] = (isset($data['need_seo']) && ($data['need_seo'] == 'on'))? 1: 0;
		}

		return $data;
	}
}