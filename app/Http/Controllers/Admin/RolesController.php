<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Roles;
use App\User;

use App\Http\Controllers\AppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolesController extends AppController
{
	/**
	 * GET|HEAD /admin/users/roles
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View|void
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug','=',$current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'user_roles');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort'=>'id', 'dir'=>'asc'];

			if(isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}

			//Get roles from DB and paginate 'em
			$roles = Roles::select(
				'id','title','slug','access_pages','editable',
				'created_at','created_by','updated_at','updated_by'
			)->orderBy($sorting_settings['sort'], $sorting_settings['dir']);

			//run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$roles = $roles->where('id','LIKE','%'.$word.'%')
						->orWhere('title','LIKE','%'.$word.'%');
				}
			}
			$roles = $roles->paginate(20);

			$content = [];
			foreach($roles as $role){
				$pages = [];
				//if user has some forbidden pages
				if($this->isJson($role->access_pages)){
					$access_pages = (array)json_decode($role->access_pages);

					foreach($access_pages as $page_id => $rules){
						$page_data = AdminMenu::select('title','slug')->find($page_id);
						$pages[] = [
							'title'	=> $page_data->title,
							'slug'	=> $page_data->slug,
							'rules'	=> $rules
						];
					}
					//If user has full access
				}else if($role->access_pages == 'grant_access'){
					$pages = 'Доступ разрешен ко всем страницам';
				}else{
					$pages = 'Доступ запрещен ко всем страницам';
				}
				//Get users with such role
				$users_list = $role->users()->select('id','name','email')->get()->toArray();
				//Get creator
				$created_by = $role->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $role->updatedBy()->select('name','email')->first();
				$content[] = [
					'id'		=> $role->id,
					'title'		=> $role->title,
					'pages'		=> $pages,
					'users'		=> $users_list,
					'editable'	=> $role->editable,
					'created'	=> date('d/ M /Y H:i', strtotime($role->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($role->updated_at)),
					'created_by'=> (!empty($created_by))
						? ['name' => $created_by->name, 'email' => $created_by->email]
						: [],
					'updated_by'=> (!empty($updated_by))
						? ['name' => $updated_by->name, 'email' => $updated_by->email]
						: [],
				];
			}

			$pagination_options = $this->createPaginationOptions($roles, $sorting_settings);

			return view('admin.roles', [
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
	 * GET|HEAD /admin/users/roles/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug','=',$current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'user_roles');

			//Get role of authorised user
			$user = Auth::user();
			$current_role = Roles::select('access_pages')->where('slug','=',$user['role'])->first();

			//Create list of forbidden pages
			$forbidden_pages = [];
			$access_pages_list = [];
			if( ($current_role->access_pages == 'grant_access') || $this->isJson($current_role->access_pages) ){
				//if user has no grant access rules
				if($this->isJson($current_role->access_pages)){
					//Get array of forbidden pages ids
					$access_pages = json_decode($current_role->access_pages);
					foreach($access_pages as $id => $access_page){
						$forbidden_pages['_'.$id] = $access_page;
					}
				}
				//Collection of pages
				$access_pages = AdminMenu::select('id','title','slug','img')
					->orderBy('refer_to','asc')
					->orderBy('position','asc')
					->get();
				//ID related list of pages
				foreach($access_pages as $access_page){
					$access_pages_list[$access_page->id] = [
						'title'	=> $access_page->title,
						'slug'	=> $access_page->slug,
						'img'	=> $access_page->img
					];
				}
			}

			return view('admin.add.roles', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Добавление '.$page->title,
				'access_pages'	=> $access_pages_list,
				'forbidden_pages' => $forbidden_pages
			]);
		}
	}


	/**
	 * GET|HEAD /admin/users/roles/{role}/edit
	 * @param $role Role ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());

		$editable_role = Roles::select('id','title','access_pages','created_by','created_at','updated_by','updated_at')
			->where('editable','!=',0)
			->find($id);

		if(($allow_access === true) && !empty($editable_role)){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'user_roles');

			//Get role of authorised user
			$user = Auth::user();
			$current_role = Roles::select('access_pages')->where('slug', '=', $user['role'])->first();

			//Create list of forbidden pages
			$forbidden_pages = [];
			$access_pages_list = [];
			if (($current_role->access_pages == 'grant_access') || $this->isJson($current_role->access_pages)) {
				//if user has no grant access rules
				if ($this->isJson($current_role->access_pages)) {
					//Get array of forbidden pages ids
					$access_pages = json_decode($current_role->access_pages);
					foreach ($access_pages as $page_id => $access_page) {
						$forbidden_pages['_' . $page_id] = $access_page;
					}
				}
				//Collection of pages
				$access_pages = AdminMenu::select('id', 'title', 'slug', 'img')
					->orderBy('refer_to', 'asc')
					->orderBy('position', 'asc')
					->get();
				//ID related list of pages
				foreach ($access_pages as $access_page) {
					$access_pages_list[$access_page->id] = [
						'title' => $access_page->title,
						'slug' => $access_page->slug,
						'img' => $access_page->img
					];
				}
			}

			//Current role access pages
			$editable_role->access_pages = json_decode($editable_role->access_pages);

			$editable_role->created_by = $editable_role->createdBy()->select('name','email')->first()->toArray();

			$editable_role->updated_by = $editable_role->updatedBy()->select('name','email')->first()->toArray();

			return view('admin.add.roles', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование роли "'.$editable_role->title.'"',
				'forbidden_pages'=> $forbidden_pages,
				'access_pages'	=> $access_pages_list,
				'content'		=> $editable_role
			]);
		}else{
			return abort(404);
		}
	}


	/**
	 * POST /admin/users/roles
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$data = $request->all();
		$user = Auth::user();

		$pages = (isset($data['ajax']))
			? $this->processAjaxCRUD($data['pages']) //if data was sent with ajax
			: $this->processCRUD($data); //if data was sent with form action

		$result = Roles::create([
			'title'=> trim($data['title']),
			'slug'=> md5($this->str2url(trim($data['title'])).uniqid()),
			'editable'=> 1,
			'access_pages'=> $pages,
			'created_by'=> $user['id'],
			'updated_by'=> $user['id']
		]);

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.roles.index');
		}
	}


	/**
	 * PUT|PATCH /admin/users/roles/{role}
	 * @param $id Role ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$data = $request->all();

		$user = Auth::user();
		if(!$user){
			return abort(503);
		}

		$pages = (isset($data['ajax']))
			? $this->processAjaxCRUD($data['pages'])
			: $this->processCRUD($data);

		$result = Roles::find($id);
		if(!empty($result)){
			$result->title			= trim($data['title']);
			$result->access_pages	= $pages;
			$result->created_by		= $user['id'];
			$result->updated_by		= $user['id'];
			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.roles.index');
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['Роль с ID #'.$id.' отсутствует']
			]);
		}
	}


	/**
	 * DELETE /admin/users/roles/{role}
	 * @param $role
	 * @return string
	 */
	public function destroy($id){
		$result = Roles::select('id','slug')->where('editable','!=',0)->find($id);
		if(!empty($result)){
			User::where('role','=',$result->slug)->update(['role'=>'']);

			$result = Roles::where('editable','!=',0)->find($id)->delete();

			if($result != false){
				return json_encode(['message'=>'success']);
			}
		}
	}

	/**
	 * Function create forbidden actions for role
	 * if user doesn't use JS in browser
	 * @param $data obj of Illuminate\Http\Request
	 * @return string
	 */
	protected function processCRUD($data){
		$permissions = [];
		if(isset($data['create'])){
			foreach($data['create'] as $id){
				if(isset($permissions[$id])){
					$permissions[$id] .= 'c';
				}else{
					$permissions[$id] = 'c';
				}
			}
		}
		if(isset($data['read'])){
			foreach($data['read'] as $id){
				if(isset($permissions[$id])){
					$permissions[$id] .= 'r';
				}else{
					$permissions[$id] = 'r';
				}
			}
		}
		if(isset($data['update'])){
			foreach($data['update'] as $id){
				if(isset($permissions[$id])){
					$permissions[$id] .= 'u';
				}else{
					$permissions[$id] = 'u';
				}
			}
		}
		if(isset($data['delete'])) {
			foreach ($data['delete'] as $id) {
				if(isset($permissions[$id])){
					$permissions[$id] .= 'd';
				}else{
					$permissions[$id] = 'd';
				}
			}
		}

		$permissions = (isset($data['read']) || isset($data['create']) || isset($data['update']) || isset($data['delete']))
			? json_encode($permissions)
			: 'grant_access';

		return $permissions;
	}


	/**
	 * Function create forbidden actions for role
	 * if data was sent by ajax
	 * @param $pages
	 * @return string
	 */
	protected function processAjaxCRUD($pages){
		$pages = json_decode($pages);
		$permissions = [];
		foreach($pages as $page_id => $rules){
			if(!empty($rules)){
				$permissions[$page_id] = $rules;
			}
		}
		$permissions = (empty($permissions))? 'grant_access': json_encode($permissions);
		return $permissions;
	}
}