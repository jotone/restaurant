<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Roles;
use App\User;

use App\Http\Controllers\AppController;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsersController extends AppController
{
	/**
	 * GET|HEAD /admin/users
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View|void
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'users');

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

			//Get users
			$users = User::select('id','name','email','role','created_at','updated_at')
				->orderBy($sorting_settings['sort'], $sorting_settings['dir']);

			//run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$users = $users->where('id','LIKE','%'.$word.'%')
						->orWhere('email','LIKE','%'.$word.'%')
						->orWhere('name','LIKE','%'.$word.'%');
				}
			}

			$users = $users->paginate(20);

			$content = [];
			foreach($users as $user){
				//Get user role
				$role = $user->role()->select('title')->first();
				//Get creator
				$created_by = $role->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $role->updatedBy()->select('name','email')->first();
				$content[] = [
					'id'	=> $user->id,
					'name'	=> $user->name,
					'email'	=> $user->email,
					'role'	=> (!empty($role))? $role->title: '',
					'created'=> date('Y /m /d H:i', strtotime($user->created_at)),
					'updated'=> date('Y /m /d H:i', strtotime($user->updated_at)),
					'created_by'=> (!empty($created_by))
						? $created_by->name.'<br>'.$created_by->email
						: $role->create_info,
					'updated_by'=> (!empty($updated_by))
						? $updated_by->name.'<br>'.$updated_by->email
						: $role->update_info,
				];
			}

			$pagination_options = $this->createPaginationOptions($users, $sorting_settings);

			return view('admin.users', [
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
	 * GET|HEAD /admin/users/create
	 * @param \Illuminate\Http\RequestRequest $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'users');

			//Get current user
			$user = Auth::user();

			if(!empty($user)){
				//Get available roles
				$roles = Roles::select('id','title','slug');
				if($user->role != 'ADM_ROOT'){
					$roles = $roles->where('slug','=',$user['role'])->orWhere('created_by','=',$user['id']);
				}
				$roles = $roles->orderBy('editable','asc')->orderBy('title','asc')->get();

				return view('admin.add.users', [
					'start'		=> $start,
					'page'		=> $request->path(),
					'breadcrumbs'=> $breadcrumbs,
					'title'		=> 'Добавление администратора',
					'roles'		=> $roles
				]);
			}
		}
	}


	/**
	 * GET|HEAD /admin/users/{user}/edit
	 * @param $id user ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'users');
			$user = User::select('id','name','email','role')->find($id);
			if(!empty($user)){
				//Get current user data
				$auth_user = Auth::user();
				//Get role of current user
				$auth_user_role = Roles::select('access_pages')->where('slug','=',$auth_user['role'])->first();
				//Get roles list
				$roles = Roles::select('id','title','slug');
				//If user has no grant access permission, he can't create grand-access user
				if($auth_user_role->access_pages != 'grant_access'){
					$roles = $roles->where('slug','=',$user['role'])->orWhere('created_by','=',$user['id'])->get();
				}
				$roles = $roles->orderBy('title','asc')->get();

				return view('admin.add.users', [
					'start'		=> $start,
					'page'		=> $request->path(),
					'breadcrumbs'=> $breadcrumbs,
					'title'		=> 'Редактирование администратора '.$user->email,
					'content'	=> $user,
					'roles'		=> $roles
				]);
			}
		}
	}


	/**
	 * GET|HEAD /admin/users/{user}
	 * @param $id user ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'users');
			$user = User::select('name','email','role')->find($id);
			if(!empty($user)){
				$roles = Roles::select('id','title','slug')->where('slug','=',$user->role)->get();

				return view('admin.add.users', [
					'start'		=> $start,
					'page'		=> $request->path(),
					'breadcrumbs'=> $breadcrumbs,
					'title'		=> 'Просмотр администратора'.$user->email,
					'content'	=> $user,
					'roles'		=>$roles
				]);
			}
		}
	}


	/**
	 * POST /admin/users
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$data = $request->all();
		$user_isset = User::where('email','=',$data['email'])->count();
		if($user_isset > 0){
			return json_encode([
				'message'	=> 'error',
				'errors'	=> ['There is user with such email']
			]);
		}
		if(!empty(trim($data['password']))){
			//validate password
			$validation = Validator::make($data, [
				'password' => 'string|min:6|confirmed',
			]);
			if($validation->fails()){
				return (isset($data['ajax']))
					//if data was sent by ajax request
					? json_encode(['message'=> 'error', 'errors' => $validation->errors()->all()])
					//if data was sent by form request
					: back()->withErrors($validation->errors());
			}
		}
		$role = Roles::select('slug')->find($data['role']);
		$result = User::create([
			'name'		=> $data['name'],
			'email'		=> $data['email'],
			'password'	=> bcrypt($data['password']),
			'role'		=> (!empty($role))? $role->slug: ''
		]);
		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.users.index');
		}
	}


	/**
	 * PUT|PATCH /admin/users/{user}
	 * @param $id user ID
	 * @param \Illuminate\Http\Request $request
	 * @return string|\Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$data = $request->all();

		if($data['role'] == '0'){
			$data['role'] = '';
		}

		$result = User::find($id);
		$users_count = User::where('id','!=',$id)->where('email','=',$data['email'])->count();
		if(!empty($result) && ($users_count < 1)){
			$role = Roles::select('slug')->find($data['role']);

			$result->name	= trim($data['name']);
			$result->email	= trim($data['email']);
			$result->role	= (!empty($role))? $role->slug: '';
			//if there is change password action
			if(!empty(trim($data['password']))){
				//validate password
				$validation = Validator::make($data, [
					'password' => 'string|min:6|confirmed',
				]);
				if($validation->fails()){
					return (isset($data['ajax']))
						//if data was sent by ajax request
						? json_encode(['message'=> 'error', 'errors' => $validation->errors()->all()])
						//if data was sent by form request
						: back()->withErrors($validation->errors());
				}
				//Encrypt password
				$result->password = bcrypt($data['password']);
			}
			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.users.index');
			}
		}else{
			return json_encode([
				'message'=>'error',
				'errors'=> ['Администратор с таким e-mail\'ом уже существует ']
			]);
		}
	}


	/**
	 * DELETE /admin/users/{user}
	 * @param $id user ID
	 * @return string
	 */
	public function destroy($id){
		$result = User::select('id')->find($id);
		if(!empty($result)){
			$result = $result->delete();
			if($result != false){
				return json_encode(['message'=>'success']);
			}
		}
	}
}