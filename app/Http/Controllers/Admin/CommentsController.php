<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Comments;
use App\News;
use App\Product;
use App\Promo;

use App\Http\Controllers\AppController;
use App\Restaurant;
use Illuminate\Http\Request;

class CommentsController extends AppController
{
	/**
	 * GET|HEAD /admin/comments
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'comments');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort'=>'created_at', 'dir'=>'desc'];
			//Process sorting income request
			if(isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}
			//Get comments by income sorting request
			$comments = Comments::orderBy($sorting_settings['sort'], $sorting_settings['dir']);
			//run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$comments = $comments->where('id','LIKE','%'.$word.'%')
						->orWhere('text','LIKE','%'.$word.'%');
				}
			}
			//Make pagination
			$comments = $comments->paginate(20);

			$content = [];
			foreach($comments as $comment){
				$post = Restaurant::select('id','title')->find($comment->post_id);
				//Get the user that left the comment
				$user = $comment->user()->select('id','name','email')->first();
				//Comment belongs to article type
				$content[] = [
					'id'		=> $comment->id,
					'user'		=> [
							'id'	=> $user->id,
							'name'	=> $user->name,
							'email'	=> $user->email
					],
					'post'		=> (!empty($post))
							? [
								'id'		=> $post->id,
								'title'		=> $post->title
							]: [],
					'text'		=> str_limit($comment->text, 32, '...'),
					'created'	=> date('d/ M /Y H:i', strtotime($comment->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($comment->updated_at))
				];
			}

			$pagination_options = $this->createPaginationOptions($comments, $sorting_settings);

			return view('admin.comments', [
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
	 * GET|HEAD /admin/comments/{id}/edit
	 * @param $id Comment ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View|void
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'comments');
			//Find comment by id
			$comment = Comments::select('id','user_id','type','post_id','refer_to_comment','text')->find($id);
			//If comment was not found
			if (empty($comment)) {
				return abort(404);
			}
			//Create editable view of comment
			$comment = $this->getCommentData($comment);

			return view('admin.add.comments', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Edit '.$page->title.' #'.$comment->id.'',
				'content'		=> $comment
			]);
		}
	}


	/**
	 * GET|HEAD /admin/comments/{id}/show
	 * @param $id Comment ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'comments');
			//Find comment by id
			$comment = Comments::select('user_id','type','post_id','refer_to_comment','text')->find($id);
			//If comment was not found
			if (empty($comment)) {
				return abort(404);
			}
			//Create editable view of comment
			$comment = $this->getCommentData($comment);

			return view('admin.add.comments', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Edit '.$page->title.' #'.$comment->id.'',
				'content'		=> $comment
			]);
		}
	}


	/**
	 * PUT|PATCH /admin/comments/{id}
	 * @param $id Comment ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$data = $request->all();

		$result = Comments::find($id);
		if(!empty($result)){
			$result->text = $data['text'];
			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.comments.index');
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['There is no comment with ID #'.$id]
			]);
		}
	}


	/**
	 * DELETE /admin/comments/{id}
	 * @param $id Comment ID
	 * @return string
	 */
	public function destroy($id){
		$result = Comments::find($id);
		Comments::where('refer_to_comment','=',$id)->update(['refer_to_comment'=>$result->refer_to_comment]);
		$result = $result->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * Function convert comment data into proper view
	 * @param $comment obj of App\Comments
	 * @return mixed obj of App\Comments
	 */
	public function getCommentData($comment){
		//Get comment creator
		$comment->user_id = (object)$comment->user()->select('id','name','email')->first()->toArray();

		//If this comment is answer to another comment
		$parent = null;
		if($comment->refer_to_comment > 0){
			$parent = Comments::select('id', 'user_id', 'text', 'created_at')->find($comment->refer_to_comment);
		}
		//If this comment doesn't belongs to another
		if( ($comment->refer_to_comment == 0) || empty($parent) ){
			$parent_comment = [];
		}else{
			$parent_comment = (object)[
				'id'	=> $parent->id,
				'user'	=> (object)$parent->user()->select('id','name','email')->first()->toArray(),
				'text'	=> $parent->text,
				'created'=> date('d/ M /Y H:i', strtotime($parent->created_at))
			];
		}
		$comment->refer_to_comment = $parent_comment;

		//Comment belongs to article type
		switch($comment->type){
			case 'news':
				$type = 'News';
				$post = News::select('id','title','created_at')->find($comment->post_id);
				break;
			case 'promo':
				$type = 'Promotions';
				$post = Promo::select('id','title','created_at')->find($comment->post_id);
				break;
			case 'products':
				$type = 'Products';
				$post = Product::select('id','title','created_at')->find($comment->post_id);
				break;
			default:
				$type = 'Unknown';
				$post = null;
		}

		//If comment has parent
		$comment->post_id = (!empty($post))
			? (object)[
				'id'		=> $post->id,
				'type'		=> $comment->type,
				'title'		=> $post->title,
				'post_type'	=> $type,
			]: [];
		return $comment;
	}
}