<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;

use App\Comments;
use App\Http\Controllers\AppController;
use App\VisitorOrder;
use App\Visitors;
use App\VisitorsRates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VisitorsController extends AppController
{
	/**
	 * GET|HEAD /admin/visitors
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'visitors');

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

			$users = Visitors::select('id','name','surname','email','phone','created_at','updated_at')
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
				$content[] = [
					'id'		=> $user->id,
					'name'		=> $user->name,
					'surname'	=> $user->surname,
					'phone'		=> $user->phone,
					'email'		=> $user->email,
					'created'	=> date('Y /m /d H:i', strtotime($user->created_at)),
					'updated'	=> date('Y /m /d H:i', strtotime($user->updated_at)),
				];
			}

			$pagination_options = $this->createPaginationOptions($users, $sorting_settings);

			return view('admin.visitors', [
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
	 * GET|HEAD /admin/visitors/{id}/edit
	 * @param $id \App\Visitors ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'visitors');
			$user = Visitors::select('id','name','surname','email','phone')->find($id);

			if(!empty($user)){
				return view('admin.add.visitors', [
					'start'		=> $start,
					'page'		=> $request->path(),
					'breadcrumbs'=> $breadcrumbs,
					'title'		=> 'Редактирование пользователя '.$user->email,
					'content'	=> $user,
				]);
			}
		}
	}

	/**
	 * GET|HEAD /admin/visitors/{id}
	 * @param $id \App\Visitors ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$breadcrumbs = $this->breadcrumbs($request->path(), 'visitors');
			$user = Visitors::select('name','surname','email','phone')->find($id);

			if(!empty($user)){
				return view('admin.add.visitors', [
					'start'		=> $start,
					'page'		=> $request->path(),
					'breadcrumbs'=> $breadcrumbs,
					'title'		=> 'Просмотр пользователя '.$user->email,
					'content'	=> $user,
				]);
			}
		}
	}

	/**
	 * PUT|PATCH /admin/visitors/{id}
	 * @param $id \App\Visitors ID
	 * @param \Illuminate\Http\Request $request
	 * @return $this|\Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$data = $request->all();

		$users_count = Visitors::where('id','!=',$id)->where('email','=',$data['email'])->count();
		if($users_count < 1){
			$result = Visitors::find($id);
			$result->name		= trim($data['name']);
			$result->surname	= trim($data['surname']);
			$result->email		= trim($data['email']);
			$result->phone		= $data['phone'];
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
				$result->password = md5($data['password']);
			}
			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.visitors.index');
			}
		}else{
			return json_encode([
				'message'=>'error',
				'errors'=> ['Пользователь с таким e-mail\'ом уже существует ']
			]);
		}
	}

	/**
	 * DELETE /adimn/visitors/{id}
	 * @param $id \App\Visitors ID
	 * @return string
	 */
	public function destroy($id){
		Comments::where('user_id','=',$id)->delete();
		VisitorOrder::where('visitor_id','=',$id)->delete();
		VisitorsRates::where('visitor_id','=',$id)->delete();
		Visitors::find($id)->delete();

		return json_encode(['message'=>'success']);
	}
}