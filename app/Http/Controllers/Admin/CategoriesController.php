<?php
namespace App\Http\Controllers\Admin;

use App\Category;
use App\CategoryTypes;

use Auth;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class CategoriesController extends AppController
{
	/**
	 * GET|HEAD /admin/category/create/{category_type}
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			//take id of category type
			$path = array_values(array_diff(explode('/', $request->path()), ['']));
			$type_id = $path[count($path)-1];

			//get category type title
			$category_type = CategoryTypes::select('title','options')->find($type_id);
			$options = json_decode($category_type->options);

			$categories = Category::select('id','title')->where('category_type','=',$type_id)->get();

			//custom breadcrumbs
			$breadcrumbs = [
				['is_link' => true, 'link' => route('admin.home'), 'title' => 'Главная'],
				['is_link' => true, 'link' => route('admin.category_types.index'), 'title' => 'Типы категорий'],
				['is_link' => true, 'link' => route('admin.category_types.edit', $type_id), 'title' => $category_type->title,],
				['is_link' => false, 'title' => 'Добавление Категории']
			];

			return view('admin.add.category', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Добавление Категории',
				'category_type'	=> $type_id,
				'categories'	=> $categories,
				'options'		=> $options
			]);
		}
	}


	/**
	 * GET|HEAD /admin/category/{category}/edit
	 * @param $id category ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_category = Category::find($id);
			//if category image is json array
			if($this->isJson($current_category->img_url)){
				$current_category->img_url = json_decode($current_category->img_url);
			}
			//Get category type
			$category_type = $current_category->category_type()->select('title','options')->first();

			$options = json_decode($category_type->options);
			//Categories referral links
			$categories = Category::select('id','title')
				->where('id','!=',$id)
				->where('category_type','=',$current_category->category_type)
				->get();

			//Get creator
			$current_category->created_by = $current_category->createdBy()->select('name','email')->first()->toArray();
			//Get updater
			$current_category->updated_by = $current_category->updatedBy()->select('name','email')->first()->toArray();

			//Custom breadcrumbs
			$breadcrumbs = [
				['is_link' => true, 'link' => route('admin.home'), 'title' => 'Главная'],
				['is_link' => true, 'link' => route('admin.category_types.index'), 'title' => 'Типы категорий'],
				['is_link' => true, 'link' => route('admin.category_types.edit', $current_category->category_type), 'title' => $category_type->title,],
				['is_link' => false, 'title' => $current_category->title]
			];
			return view('admin.add.category', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Редактирование Категории',
				'category_type'	=> $current_category->category_type,
				'categories'	=> $categories,
				'content'		=> $current_category,
				'options'		=> $options
			]);
		}
	}


	/**
	 * POST /admin/category
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$data = $request->all();

		$user = Auth::user();

		//Convert data to acceptable notion
		$data = $this->processData($data);
		//Make slug link unique
		if(Category::where('slug','=',$data['slug'])->count() > 0){
			$data['slug'] = trim($data['slug']).'_'.uniqid();
		}

		/*TODO: make image 300x300*/

		//If image was sent as $_FILE value
		if((!empty($request->file())) && ($request->file('image')->isValid())){
			$img_url = json_encode([
				'src' => $this->createImg($request->file('image')),
				'alt' => ''
			]);
		//if image was sent as base64encoded file content
		}else if(isset($data['image']) && $this->isJson($data['image'])){
			$temp = json_decode($data['image']);
			$img_url = json_encode([
				'src' => ($temp->type == 'upload')
					? $this->createImgBase64($temp->src)
					: $temp->src,
				'alt' => ''
			]);
		}else{
			$img_url = '';
		}


		//Create category position
		$position = 0;
		//Get last position of category in group and category type
		$category_position = Category::select('position')
			->where('category_type','=',$data['category_type'])
			->where('refer_to','=',$data['refer_to'])
			->orderBy('position','desc')
			->first();
		if(!empty($category_position)){
			$position = $category_position->position;
		}

		//Save category
		$result = Category::create([
			'title'				=> trim($data['title']),
			'slug'				=> str_slug(trim($data['slug'])),
			'text'				=> trim($data['text']),
			'img_url'			=> $img_url,
			'meta_title'		=> $data['meta_title'],
			'meta_description'	=> $data['meta_description'],
			'meta_keywords'		=> $data['meta_keywords'],
			'need_seo'			=> $data['need_seo'],
			'seo_title'			=> $data['seo_title'],
			'seo_text'			=> $data['seo_text'],
			'enabled'			=> $data['enabled'],
			'category_type'		=> $data['category_type'],
			'refer_to'			=> $data['refer_to'],
			'position'			=> $position,
			'created_by'		=> $user['id'],
			'updated_by'		=> $user['id']
		]);

		if($result != false){
			return (isset($data['ajax']))
				//data was sent by ajax request
				? json_encode(['message'=>'success'])
				//data was sent by form
				: redirect()->route('admin.category_types.edit', $data['category_type']);
		}
	}


	/**
	 * PUT /admin/category/{category}
	 * @param $id category ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$data = $request->all();
		//Get current user
		$user = Auth::user();
		//Convert data to acceptable notion
		$data = $this->processData($data);

		//Make slug link unique
		if(Category::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0){
			$data['slug'] = trim($data['slug']).'_'.uniqid();
		}

		//If image was sent as $_FILE value
		if((!empty($request->file())) && ($request->file('image')->isValid())){
			$img_url = json_encode([
				'src' => $this->makeSquareImage($this->createImg($request->file('image'))),
				'alt' => ''
			]);
		//if image was sent as base64encoded file content
		}else if(isset($data['image']) && $this->isJson($data['image'])){
			$temp = json_decode($data['image']);
			$img_url = json_encode([
				'src' => ($temp->type == 'upload')
					? $this->makeSquareImage($this->createImgBase64($temp->src))
					: $temp->src,
				'alt' => ''
			]);
		}else{
			$img_url = '';
		}


		//Update category data
		$result = Category::find($id);
		if(!empty($result)){
			$result->title			= trim($data['title']);
			$result->slug			= str_slug(trim($data['slug']));
			$result->text			= trim($data['text']);
			$result->img_url		= $img_url;
			$result->meta_title		= $data['meta_title'];
			$result->meta_description= $data['meta_description'];
			$result->meta_keywords	= $data['meta_keywords'];
			$result->need_seo		= $data['need_seo'];
			$result->seo_title		= $data['seo_title'];
			$result->seo_text		= $data['seo_text'];
			$result->enabled		= $data['enabled'];
			$result->refer_to		= $data['refer_to'];
			$result->updated_by		= $user['id'];
			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.category_types.edit', $data['category_type']);
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['Категория с ID #'.$id.' отсутствует']
			]);
		}
	}


	/**
	 * PATCH /admin/category/{category}
	 * @param $id category ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function replace($id, Request $request){
		$data = $request->all();
		//Change enabled status of category
		$result = Category::find($id);
		if(isset($data['enabled'])){
			$result->enabled = $data['enabled'];
		}
		$result->save();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * DELETE /admin/category/{category}
	 * @param $id category ID
	 * @return string
	 */
	public function destroy($id){
		$result = Category::find($id);
		//change referral links of children categories
		Category::where('category_type','=',$result->category_type)
			->where('refer_to','=',$id)
			->update(['refer_to'=>$result->refer_to]);
		//Drop category
		$result = $result->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * @param $data is \Illuminate\Http\Request $request->all()
	 * @return mixed $data
	 */
	protected function processData($data){
		//Create slug link
		if(empty($data['slug'])){
			$data['slug'] = $this->str2url(trim($data['title']));
		}

		$data['text'] = (!empty($data['text']))? $data['text']: null;
		$data['meta_title'] = (!empty($data['meta_title']))? $data['meta_title']: null;
		$data['meta_keywords'] = (!empty($data['meta_keywords']))? $data['meta_keywords']: null;
		$data['meta_description'] = (!empty($data['meta_description']))? $data['meta_description']: null;

		if(isset($data['need_seo'])){
			$data['seo_title'] = trim($data['seo_title']);
			$data['seo_text'] = trim($data['seo_text']);
		}else{
			$data['seo_title'] = $data['seo_text'] = null;
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