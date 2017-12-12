<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Template;

use Auth;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class TemplatesController extends AppController
{
	/**
	 * GET|HEAD /admin/pages/templates/
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'templates');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort' => 'title', 'dir' => 'asc'];
			//Process sorting income request
			if (isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}

			//Get news by income sorting request
			$templates = Template::orderBy($sorting_settings['sort'], $sorting_settings['dir']);
			//run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$templates = $templates->where('id','LIKE','%'.$word.'%')
						->orWhere('title','LIKE','%'.$word.'%')
						->orWhere('slug','LIKE','%'.$word.'%');
				}
			}
			//Make pagination
			$templates = $templates->paginate(20);

			$content = [];
			foreach($templates as $template){
				//Get creator
				$created_by = $template->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $template->updatedBy()->select('name','email')->first();
				$used_in = $template->pages()->select('id','title')->orderBy('title','asc')->get();
				$content[] = [
					'id'		=> $template->id,
					'title'		=> $template->title,
					'used_in'	=> (!empty($used_in->all()))? $used_in->toArray(): [],
					'created'	=> date('d/ M /Y H:i', strtotime($template->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($template->updated_at)),
					'created_by'=> (!empty($created_by))
								? ['name' => $created_by->name, 'email' => $created_by->email]
								: [],
					'updated_by'=> (!empty($updated_by))
								? ['name' => $updated_by->name, 'email' => $updated_by->email]
								: [],
				];
			}

			$pagination_options = $this->createPaginationOptions($templates, $sorting_settings);

			return view('admin.templates', [
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
	 * GET|HEAD /admin/pages/templates/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'templates');

			return view('admin.add.templates', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Добавление Шаблона',
			]);
		}
	}


	/**
	 * GET|HEAD /admin/pages/templates/{id}/edit
	 * @param $id Template ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'templates');

			$content = Template::select('id','title','html_content','enabled','created_by','created_at','updated_by','updated_at')->find($id);
			//Get creator
			$content->created_by = $content->createdBy()->select('name','email')->first();
			//Get updater
			$content->updated_by = $content->updatedBy()->select('name','email')->first();

			return view('admin.add.templates', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование шаблона "'.$content->title.'"',
				'content'		=> $content
			]);
		}
	}


	/**
	 * GET|HEAD /admin/pages/templates/{id}
	 * @param $id Template ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'templates');

			$content = Template::select('title','html_content','enabled','created_by','created_at','updated_by','updated_at')->find($id);
			//Get creator
			$content->created_by = $content->createdBy()->select('name','email')->first();
			//Get updater
			$content->updated_by = $content->updatedBy()->select('name','email')->first();

			return view('admin.add.templates', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Просмотр шаблона "'.$content->title.'"',
				'content'		=> $content
			]);
		}
	}


	/**
	 * POST /admin/pages/templates
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$user = Auth::user();
		$data = $this->processData($request);

		$data['slug'] = (Template::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Template::create([
			'title'			=> $data['title'],
			'slug'			=> $data['slug'],
			'html_content'	=> $data['html_content'],
			'enabled'		=> $data['enabled'],
			'created_by'	=> $user['id'],
			'updated_by'	=> $user['id'],
		]);

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.templates.index');
		}
	}


	/**
	 * PUT|PATCH /admin/pages/templates/{id}
	 * @param $id Template ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$user = Auth::user();
		$data = $this->processData($request);

		$data['slug'] = (Template::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Template::find($id);
		if(!empty($result)){
			$result->title			= $data['title'];
			$result->slug			= $data['slug'];
			$result->html_content	= $data['html_content'];
			$result->enabled		= $data['enabled'];
			$result->updated_by		= $user['id'];
			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.templates.index');
			}
		}else{
			return json_encode([
				'message' => 'error',
				'errors' => ['Шаблон с ID #'.$id.' отсутствует']
			]);
		}
	}


	/**
	 * DELETE /admin/pages/templates/{id}
	 * @param $id Template ID
	 * @return string
	 */
	public function destroy($id){
		$result = Template::find($id)->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * @param \Illuminate\Http\Request $request
	 * @return mixed $data
	 */
	public function processData($request){
		$data = $request->all();

		$data['slug'] = str_slug($this->str2url($data['title']));

		//Change checkbox values to boolean
		if(isset($data['ajax'])){
			$data['enabled'] = (isset($data['enabled']))? $data['enabled']: 0;
		}else{
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on'))? 1: 0;
		}

		return $data;
	}
}