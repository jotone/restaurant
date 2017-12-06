<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\Product;
use App\Settings;
use App\Tags;

use Auth;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class ProductController extends AppController
{
	/**
	 * GET|HEAD /admin/products
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug','=',$current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'products');

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
			//Get products by income sorting request
			$products = Product::select(
				'id','title','slug','description','text','vendor_code','img_url','price','quantity','rating',
				'category_id','tags','views','published_at',
				'created_at','created_by','updated_at','updated_by'
			)->orderBy($sorting_settings['sort'], $sorting_settings['dir']);
			//Run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$products = $products->where('id','LIKE','%'.$word.'%')
						->orWhere('vendor_code','LIKE','%'.$word.'%')
						->orWhere('title','LIKE','%'.$word.'%')
						->orWhere('slug','LIKE','%'.$word.'%')
						->orWhere('description','LIKE','%'.$word.'%')
						->orWhere('text','LIKE','%'.$word.'%');
				}
			}
			//Make pagination
			$products = $products->paginate(20);

			$content = [];
			foreach($products as $product){
				//Get current categories
				$product->category_id = json_decode($product->category_id);
				$categories_list = [];
				foreach($product->category_id as $category_id){
					$category = Category::select('id','title')->find($category_id);
					if(!empty($category)){
						$categories_list[$category->id] = $category->title;
					}
				}
				//Get product tags
				$tags = ($this->isJson($product->tags))? json_decode($product->tags): null;
				$tag_list = [];
				if(!empty($tags)){
					foreach($tags as $tag){
						$tag = Tags::select('title')->find($tag);
						$tag_list[] = $tag->title;
					}
				}
				//Get image
				$image = ($this->isJson($product->img_url))? json_decode($product->img_url): null;

				//Get creator
				$created_by = $product->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $product->updatedBy()->select('name','email')->first();
				//Create products list
				$content[] = [
					'id'		=> $product->id,
					'title'		=> $product->title,
					'slug'		=> $product->slug,
					'vendor_code'=> $product->vendor_code,
					'img_url'	=> (!empty($image))? $image[0]: null,
					'price'		=> (!empty($product->price))? $product->price: 0,
					'quantity'	=> $product->quantity,
					'rating'	=> $product->rating,
					'category'	=> (!empty($categories_list))? $categories_list: ['No category'],
					'tags'		=> implode(', ', $tag_list),
					'views'		=> $product->views,
					'published'	=> date('d/ M /Y H:i', strtotime($product->published_at)),
					'created'	=> date('d/ M /Y H:i', strtotime($product->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($product->updated_at)),
					'created_by'=> (!empty($created_by))
									? ['name' => $created_by->name, 'email' => $created_by->email]
									: [],
					'updated_by'=> (!empty($updated_by))
									? ['name' => $updated_by->name, 'email' => $updated_by->email]
									: [],
				];
			}
			$pagination_options = $this->createPaginationOptions($products, $sorting_settings);
			return view('admin.products', [
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
	 * GET|HEAD /admin/products/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'products');

			//Get products settings
			$settings = Settings::select('options')->where('slug','=','products')->first()->toArray();
			$settings = json_decode($settings['options']);

			//Get categories list
			$categories = $this->categoriesReferals($settings->category_type);

			return view('admin.add.products', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Add '.$page->title,
				'categories'	=> $categories,
				'settings'		=> $settings,
			]);
		}
	}


	/**
	 * GET|HEAD /admin/product/{id}/edit
	 * @param $id Product ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'products');
			//Find products by id
			$product = Product::select(
				'id','title','slug','vendor_code','description','text','characteristic','img_url','price','quantity',
				'rating','tags','refer_to_promo','meta_title','meta_description','meta_keywords',
				'seo_title','seo_text','category_id','enabled',
				'created_by','created_at','updated_by','updated_at'
				)->find($id);
			//If products was not found
			if(empty($product)){
				return abort(404);
			}
			//Convert product data
			$product = $this->getProductData($product);

			//Get products settings
			$settings = Settings::select('options')->where('slug','=','products')->first()->toArray();
			$settings = json_decode($settings['options']);
			//Get categories list
			$categories = $this->categoriesReferals($settings->category_type);

			return view('admin.add.products', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Edit '.$page->title.' "'.$product->title.'"',
				'categories'	=> $categories,
				'settings'		=> $settings,
				'content'		=> $product
			]);
		}
	}


	/**
	 * GET|HEAD /admin/product/{id}/show
	 * @param $id Product ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'products');
			//Find products by id
			$product = Product::select(
				'title','slug','vendor_code','description','text','characteristic','img_url','price','quantity',
				'rating','tags','refer_to_promo','meta_title','meta_description','meta_keywords',
				'seo_title','seo_text','category_id','enabled',
				'created_by','created_at','updated_by','updated_at'
			)->find($id);
			//If products was not found
			if (empty($product)) {
				return abort(404);
			}
			//Convert product data
			$product = $this->getProductData($product);

			//Get products settings
			$settings = Settings::select('options')->where('slug','=','products')->first()->toArray();
			$settings = json_decode($settings['options']);
			//Get categories list
			$categories = $this->categoriesReferals($settings->category_type);

			return view('admin.add.products', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'View '.$page->title.' "'.$product->title.'"',
				'categories'	=> $categories,
				'settings'		=> $settings,
				'content'		=> $product
			]);
		}
	}


	/**
	 * POST /admin/products
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$user = Auth::user();
		//Transform request data into proper
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['images'];//Slider images
		$tags = $temp['tags'];//Product tags
		$characteristic = $temp['characteristic'];//Product characteristics

		//If there are products with such link
		$data['slug'] = (Product::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Product::create([
			'title'				=> $data['title'],
			'slug'				=> $data['slug'],
			'vendor_code'		=> $data['vendor_code'],
			'description'		=> $data['description'],
			'text'				=> $data['text'],
			'characteristic'	=> json_encode($characteristic),
			'price'				=> str_replace(',','.',$data['price']),
			'quantity'			=> $data['quantity'],
			'img_url'			=> json_encode($img_url),
			'tags'				=> json_encode($tags),
			'rating'			=> (isset($data['rating']))? $data['rating']: 0,
			'likes_dislikes'	=> json_encode(['likes'=> 0, 'dislikes'=> 0]),
			'meta_title'		=> $data['meta_title'],
			'meta_description'	=> $data['meta_description'],
			'meta_keywords'		=> $data['meta_keywords'],
			'seo_title'			=> $data['seo_title'],
			'seo_text'			=> $data['seo_text'],
			'enabled'			=> $data['enabled'],
			'category_id'		=> (isset($data['category']))? $data['category']: 0,
			'refer_to_promo'	=> 0,
			'created_by'		=> $user['id'],
			'updated_by'		=> $user['id']
		]);

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.products.index');
		}
	}


	/**
	 * PUT|PATCH /admin/products/{id}
	 * @param $id Product ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$user = Auth::user();
		//Transform request data into proper
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['images'];//Slider images
		$tags = $temp['tags'];//Product tags
		$characteristic = $temp['characteristic'];//Product characteristics

		//If there are products with such link
		$data['slug'] = (Product::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Product::find($id);
		if(!empty($result)){
			$result->title			= $data['title'];
			$result->slug			= $data['slug'];
			$result->vendor_code	= $data['vendor_code'];
			$result->description	= $data['description'];
			$result->text			= $data['text'];
			$result->characteristic	= json_encode($characteristic);
			$result->price			= str_replace(',','.',$data['price']);
			$result->quantity		= $data['quantity'];
			$result->img_url		= json_encode($img_url);
			$result->tags			= json_encode($tags);
			$result->rating			= (isset($data['rating']))? $data['rating']: 0;
			$result->meta_title		= $data['meta_title'];
			$result->meta_description= $data['meta_description'];
			$result->meta_keywords	= $data['meta_keywords'];
			$result->seo_title		= $data['seo_title'];
			$result->seo_text		= $data['seo_text'];
			$result->enabled		= $data['enabled'];
			$result->category_id	= (isset($data['category']))? $data['category']: 0;
			$result->refer_to_promo	= 0;
			$result->updated_by		= $user['id'];
			$result->save();

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.products.index');
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['There is no product with ID #'.$id]
			]);
		}
	}


	/**
	 * DELETE /admin/products/{id}
	 * @param $id Product ID
	 * @return string
	 */
	public function destroy($id){
		$result = Product::find($id)->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * Function convert product data to proper view
	 * @param \App\Product $product
	 * @return \App\Product $product
	 */
	public function getProductData($product){
		//Create images array
		$images = [];
		if($this->isJson($product->img_url)){
			$product->img_url = json_decode($product->img_url);
			foreach($product->img_url as $image){
				$name = $this->getFileName($image->src);
				$images[] = [
					'src'		=> $image->src,
					'alt'		=> (isset($image->alt))? $image->alt: '',
					'name'		=> $name,
					'size'		=> self::niceFilesize(base_path().'/public'.$image->src)
				];
			}
		}
		$product->img_url = $images;
		//Create tags array
		$product->tags = json_decode($product->tags);
		$tags = [];
		foreach($product->tags as $tag_id){
			$tag = Tags::select('title')->find($tag_id);
			$tags[] = $tag->title;
		}
		$product->tags = implode(', ', $tags);
		//Create news categories
		$product->category_id = json_decode($product->category_id);
		//characteristics
		$product->characteristic = json_decode($product->characteristic);
		//Get creator
		$product->created_by = $product->createdBy()->select('name','email')->first();
		//Get updater
		$product->updated_by = $product->updatedBy()->select('name','email')->first();

		return $product;
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

		$data['vendor_code']	= (isset($data['vendor_code']))? $data['vendor_code']: '';
		$data['price']			= (isset($data['price']))? $data['price']: '';
		$data['quantity']		= (isset($data['quantity']))? $data['quantity']: 0;
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

		//Creating characteristics array
		$characteristic = [];
		if(isset($data['ajax'])){
			$characteristic = isset($data['characteristic'])? $data['characteristic']: [];
		}else{
			if(isset($data['rowCaption'])){
				foreach($data['rowCaption'] as $i => $row){
					$characteristic[] = [
						'key' => $row,
						'val' => (isset($data['rowValue'][$i]))? $data['rowValue'][$i]: ''
					];
				}
			}
		}

		return [
			'data' => $data,
			'images' => $img_url,
			'tags' => $tags,
			'characteristic' => $characteristic
		];
	}
}