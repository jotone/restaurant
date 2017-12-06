<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\News;
use App\Settings;
use App\Tags;

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class NewsController extends AppController
{
	/**
	 * GET|HEAD /admin/news
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug','=',$current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'news');

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
			//Get news by income sorting request
			$news = News::select(
				'id','title','slug','img_url','text','description','category_id','tags','views','published_at',
				'created_at','created_by','updated_at','updated_by'
			)->orderBy($sorting_settings['sort'], $sorting_settings['dir']);
			//run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$news = $news->where('id','LIKE','%'.$word.'%')
						->orWhere('title','LIKE','%'.$word.'%')
						->orWhere('slug','LIKE','%'.$word.'%')
						->orWhere('description','LIKE','%'.$word.'%')
						->orWhere('text','LIKE','%'.$word.'%');
				}
			}
			//Make pagination
			$news = $news->paginate(20);

			$content = [];
			foreach($news as $new){
				//Get current categories
				$new->category_id = json_decode($new->category_id);
				$categories_list = [];
				foreach($new->category_id as $category_id){
					$category = Category::select('id','title')->find($category_id);
					if(!empty($category)){
						$categories_list[$category->id] = $category->title;
					}
				}
				//Get news tags
				$tags = ($this->isJson($new->tags))? json_decode($new->tags): null;
				$tag_list = [];
				if(!empty($tags)){
					foreach($tags as $tag){
						$tag = Tags::select('title')->find($tag);
						$tag_list[] = $tag->title;
					}
				}
				//Get image
				$image = ($this->isJson($new->img_url))? json_decode($new->img_url): null;

				//Get creator
				$created_by = $new->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $new->updatedBy()->select('name','email')->first();
				//Create news list
				$content[] = [
					'id'		=> $new->id,
					'title'		=> $new->title,
					'slug'		=> $new->slug,
					'img_url'	=> (!empty($image))? $image[0]: null,
					'category'	=> (!empty($categories_list))? $categories_list: ['No category'],
					'tags'		=> implode(', ', $tag_list),
					'views'		=> $new->views,
					'published'	=> date('d/ M /Y H:i', strtotime($new->published_at)),
					'created'	=> date('d/ M /Y H:i', strtotime($new->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($new->updated_at)),
					'created_by'=> (!empty($created_by))
									? ['name' => $created_by->name, 'email' => $created_by->email]
									: [],
					'updated_by'=> (!empty($updated_by))
									? ['name' => $updated_by->name, 'email' => $updated_by->email]
									: [],
				];
			}

			$pagination_options = $this->createPaginationOptions($news, $sorting_settings);

			return view('admin.news', [
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
	 * GET|HEAD /admin/news/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'news');

			//Get news settings
			$settings = Settings::select('options')->where('slug','=','news')->first()->toArray();
			$settings = json_decode($settings['options']);

			//Get categories list
			$categories = $this->categoriesReferals($settings->category_type);

			return view('admin.add.news', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Add '.$page->title,
				'categories'	=> $categories,
				'settings'		=> $settings
			]);
		}
	}


	/**
	 * GET|HEAD /admin/news/{id}/edit
	 * @param $id news ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'news');
			//Find news by id
			$news = News::select(
				'id','title','slug','description','text','img_url','tags',
				'meta_title','meta_keywords','meta_description',
				'seo_title','seo_text','category_id','enabled',
				'created_by','created_at','updated_by','updated_at'
			)->find($id);
			//If news was not found
			if(empty($news)){
				return abort(404);
			}
			//Convert news data
			$news = $this->getNewsData($news);

			//Get news settings
			$settings = Settings::select('options')->where('slug','=','news')->first()->toArray();
			$settings = json_decode($settings['options']);

			//Get categories list
			$categories = $this->categoriesReferals($settings->category_type);

			return view('admin.add.news', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Edit '.$page->title.' "'.$news->title.'"',
				'categories'	=> $categories,
				'settings'		=> $settings,
				'content'		=> $news,
			]);
		}
	}


	/**
	 * GET|HEAD /admin/news/{id}/show
	 * @param $id news ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'news');
			//Find news by id
			$news = News::select(
				'title','slug','description','text','img_url','tags',
				'meta_title','meta_keywords','meta_description',
				'seo_title','seo_text','category_id','enabled',
				'created_by','created_at','updated_by','updated_at'
			)->find($id);
			//If news was not found
			if(empty($news)){
				return abort(404);
			}
			//Convert news data
			$news = $this->getNewsData($news);

			//Get news settings
			$settings = Settings::select('options')->where('slug','=','news')->first()->toArray();
			$settings = json_decode($settings['options']);

			//Get categories list
			$categories = $this->categoriesReferals($settings->category_type);

			return view('admin.add.news', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'View '.$page->title.' "'.$news->title.'"',
				'categories'	=> $categories,
				'settings'		=> $settings,
				'content'		=> $news
			]);
		}
	}


	/**
	 * POST /admin/news
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		//Transform request data into proper
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['images'];
		$tags = $temp['tags'];
		//Get current user data
		$user = Auth::user();

		//If there are news with such link
		$data['slug'] = (News::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = News::create([
			'title'				=> trim($data['title']),
			'slug'				=> str_slug(trim($data['slug'])),
			'description'		=> $data['description'],
			'text'				=> trim($data['text']),
			'img_url'			=> json_encode($img_url),
			'tags'				=> json_encode($tags),
			'author'			=> $data['author'],
			'meta_title'		=> $data['meta_title'],
			'meta_description'	=> $data['meta_description'],
			'meta_keywords'		=> $data['meta_keywords'],
			'seo_title'			=> $data['seo_title'],
			'seo_text'			=> $data['seo_text'],
			'enabled'			=> $data['enabled'],
			'category_id'		=> $data['category'],
			'created_by'		=> $user['id'],
			'updated_by'		=> $user['id']
		]);

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.news.index');
		}
	}


	/**
	 * PUT|PATCH /admin/news/{id}
	 * @param $id news ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$user = Auth::user();
		//Transform request data into proper
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['images'];
		$tags = $temp['tags'];

		//If there are news with such link
		$data['slug'] = (News::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = News::find($id);
		if(!empty($result)){
			$result->title			= trim($data['title']);
			$result->slug			= str_slug(trim($data['slug']));
			$result->description	= $data['description'];
			$result->text			= trim($data['text']);
			$result->img_url		= json_encode($img_url);
			$result->tags			= json_encode($tags);
			$result->author			= $data['author'];
			$result->meta_title		= $data['meta_title'];
			$result->meta_description= $data['meta_description'];
			$result->meta_keywords	= $data['meta_keywords'];
			$result->seo_title		= $data['seo_title'];
			$result->seo_text		= $data['seo_text'];
			$result->enabled		= $data['enabled'];
			$result->category_id	= (isset($data['category']))? $data['category']: 0;
			$result->updated_by		= $user['id'];
			$result->save();
			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.news.index');
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['There is no news with ID #'.$id]
			]);
		}
	}


	/**
	 * DELETE /admin/news/{id}
	 * @param $id news ID
	 * @return string
	 */
	public function destroy($id){
		$result = News::find($id)->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * Function convert news data into proper view
	 * @param $news obj of App\News
	 * @return mixed obj of App\News
	 */
	public function getNewsData($news){
		//Create images array
		$news->img_url = json_decode($news->img_url);
		$images = [];
		foreach($news->img_url as $image){
			$name = $this->getFileName($image->src);
			$images[] = [
				'src'		=> $image->src,
				'alt'		=> (isset($image->alt))? $image->alt: '',
				'name'		=> $name,
				'size'		=> self::niceFilesize(base_path().'/public'.$image->src)
			];
		}
		$news->img_url = $images;
		//Create tags array
		$news->tags = json_decode($news->tags);
		$tags = [];
		foreach($news->tags as $tag_id){
			$tag = Tags::select('title')->find($tag_id);
			$tags[] = $tag->title;
		}
		$news->tags = implode(', ', $tags);
		//Create news categories
		$news->category_id = json_decode($news->category_id);
		//Get creator
		$news->created_by = $news->createdBy()->select('name','email')->first();
		//Get updater
		$news->updated_by = $news->updatedBy()->select('name','email')->first();

		return $news;
	}


	/**
	 * @param \Illuminate\Http\Request $request
	 * @return mixed $data
	 */
	public function processData($request){
		$data = $request->all();
		//Create slug link
		if(empty($data['slug'])){
			$data['slug'] = $this->str2url(trim($data['title']));
		}

		$data['description']	= (isset($data['description']))? $data['description']: '';
		$data['text']			= (isset($data['text']))? $data['text']: '';
		$data['meta_title']		= (isset($data['meta_title']))? $data['meta_title']: '';
		$data['meta_description']=(isset($data['meta_description']))? $data['meta_description']: '';
		$data['meta_keywords']	= (isset($data['meta_keywords']))? $data['meta_keywords']: '';
		$data['seo_title']		= (isset($data['seo_title']))? $data['seo_title']: '';
		$data['seo_text']		= (isset($data['seo_text']))? $data['seo_text']: '';

		//Create Category
		if(isset($data['category'])){
			$data['category'] =(is_array($data['category']))
				? json_encode($data['category'])
				: '["'.$data['category'].'"]';
		}else{
			$data['category'] = '["0"]';
		}

		//Change checkbox values to boolean
		if(isset($data['ajax'])){
			$data['enabled'] = (isset($data['enabled']))? $data['enabled']: 0;
		}else{
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on'))? 1: 0;
		}
		//Create images array
		$img_url = [];
		//If image data was sent by ajax
		if(isset($data['ajax']) && isset($data['images'])){
			foreach($data['images'] as $image){
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
		//Creating tags array
		if(isset($data['tags']) && !empty($data['tags'])){
			$tags = explode(',',$data['tags']);
			$tags = array_map(function($item){
				return TagController::createTag($item);
			}, $tags);
		}else{
			$tags = [];
		}
		return [
			'data' => $data,
			'images' => $img_url,
			'tags' => $tags
		];
	}


	/**
	 * Function will find tag title by request string
	 * @param $tag title of tag
	 * @return string json response
	 */
	public function getTag($tag){
		$tag = mb_strtolower($tag);
		$searched_tags = Tags::select('title')->where('title', 'LIKE', $tag.'%')->get();
		$result = [];
		foreach($searched_tags as $tag){
			$result[] = $tag->title;
		}
		return json_encode([
			'message'	=> (empty($result))? 'nope': 'success',
			'result'	=> $result
		]);
	}
}