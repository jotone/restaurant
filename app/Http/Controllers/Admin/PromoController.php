<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;
use App\Category;
use App\Product;
use App\Promo;
use App\Settings;

use Auth;
use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class PromoController extends AppController
{
	/**
	 * GET|HEAD /admin/promo
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug','=',$current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'promo');

			//Get request data
			$request_data = $request->all();
			//Default sorting settings
			$sorting_settings = ['sort'=>'date_finish', 'dir'=>'desc'];
			//Process sorting income request
			if(isset($request_data['sort_by'])) {
				$sorting_settings = [
					'sort' => $request_data['sort_by'],
					'dir' => (isset($request_data['dir']) && $request_data['dir'] == 'asc') ? 'asc' : 'desc'
				];
			}
			//Get products by income sorting request
			$promos = Promo::orderBy($sorting_settings['sort'], $sorting_settings['dir']);
			//Run search request
			if(isset($request_data['search']) && !empty(trim($request_data['search']))){
				$search = explode(' ', $request_data['search']);
				foreach($search as $word){
					$promos = $promos->where('id','LIKE','%'.$word.'%')
						->orWhere('title','LIKE','%'.$word.'%')
						->orWhere('slug','LIKE','%'.$word.'%');
				}
			}
			//Make pagination
			$promos = $promos->paginate(20);

			$content = [];
			foreach($promos as $promo){
				$image = ($this->isJson($promo->img_url))? json_decode($promo->img_url): null;
				//Get products in promo
				$products = $promo->products()->select('id','title')->orderBy('title','asc')->get();

				//Get creator
				$created_by = $promo->createdBy()->select('name','email')->first();
				//Get updater
				$updated_by = $promo->updatedBy()->select('name','email')->first();

				$content[] = [
					'id'		=> $promo->id,
					'title'		=> $promo->title,
					'slug'		=> $promo->slug,
					'img_url'	=> (!empty($image))? $image[0]: null,
					'start'		=> date('d/ M /Y H:i', strtotime($promo->date_start)),
					'finish'	=> date('d/ M /Y H:i', strtotime($promo->date_finish)),
					'discount'	=> ($promo->discount_type == 0)? $promo->discount.'%': '-'.$promo->discount,
					'products'	=> $products,
					'views'		=> $promo->views,
					'published'	=> date('d/ M /Y H:i', strtotime($promo->published_at)),
					'created'	=> date('d/ M /Y H:i', strtotime($promo->created_at)),
					'updated'	=> date('d/ M /Y H:i', strtotime($promo->updated_at)),
					'created_by'=> (!empty($created_by))
								? ['name' => $created_by->name, 'email' => $created_by->email]
								: [],
					'updated_by'=> (!empty($updated_by))
								? ['name' => $updated_by->name, 'email' => $updated_by->email]
								: [],
				];
			}

			$pagination_options = $this->createPaginationOptions($promos, $sorting_settings);
			return view('admin.promo', [
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
	 * GET|HEAD /admin/promo/create
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function create(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'promos');

			//Create product list
			$products = Product::select('id','title','img_url','price','category_id')
				->where('enabled','=',1)
				->orderBy('category_id','desc')
				->get();
			$product_list = [];
			foreach($products as $product){
				//Get image
				$image = ($this->isJson($product->img_url))? json_decode($product->img_url): null;
				//Get category
				$category = Category::select('title')->find($product->category_id);
				$product_list[$product->category_id]['caption'] = (!empty($category))? $category->title: 'No category';
				$product_list[$product->category_id]['items'][] = [
					'id'		=> $product->id,
					'title'		=> $product->title,
					'price'		=> number_format((float)$product->price, 2, '.', ' ')
				];
			}

			//Get promo settings
			$settings = Settings::select('options')->where('slug','=','promo')->first()->toArray();
			$settings = json_decode($settings['options']);

			return view('admin.add.promo', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Add '.$page->title,
				'products'		=> $product_list,
				'settings'		=> $settings
			]);
		}
	}


	/**
	 * GET|HEAD /admin/promo/{id}/edit
	 * @param $id Promo ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function edit($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'promos');

			//Get Promo data
			$promo = Promo::select(
				'id','title','slug','img_url','description','text','date_start','date_finish','discount_type','discount',
				'meta_title','meta_keywords','meta_description',
				'seo_title','seo_text','enabled','created_by','updated_by','created_at','updated_at'
			)->find($id);

			if(empty($promo)){
				return abort(404);
			}

			$promo = $this->getPromoData($promo);

			//Create product list
			$products = Product::select('id','title','img_url','price','category_id')
				->where('enabled','=',1)
				->orderBy('category_id','desc')
				->get();
			$product_list = [];
			foreach($products as $product){
				//Get category
				$category = Category::select('title')->find($product->category_id);
				$product_list[$product->category_id]['caption'] = (!empty($category))? $category->title: 'No category';
				$product_list[$product->category_id]['items'][] = [
					'id'		=> $product->id,
					'title'		=> $product->title,
					'price'		=> number_format((float)$product->price, 2, '.', ' ')
				];
			}

			//Get promo settings
			$settings = Settings::select('options')->where('slug','=','promo')->first()->toArray();
			$settings = json_decode($settings['options']);

			return view('admin.add.promo', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Edit '.$page->title.' "'.$promo->title.'"',
				'products'		=> $product_list,
				'settings'		=> $settings,
				'content'		=> $promo
			]);
		}
	}


	/**
	 * GET|HEAD /admin/promo/{id}/show
	 * @param $id Promo ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\View\View
	 */
	public function show($id, Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true) {
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($request->path(), 'promos');

			//Get Promo data
			$promo = Promo::select(
				'id','title','slug','img_url','description','text','date_start','date_finish','discount_type','discount',
				'meta_title','meta_keywords','meta_description',
				'seo_title','seo_text','enabled','created_by','updated_by','created_at','updated_at'
			)->find($id);

			if(empty($promo)){
				return abort(404);
			}

			$promo = $this->getPromoData($promo);

			//Create product list
			$products = Product::select('id','title','img_url','price','category_id')
				->where('enabled','=',1)
				->orderBy('category_id','desc')
				->get();
			$product_list = [];
			foreach($products as $product){
				//Get category
				$category = Category::select('title')->find($product->category_id);
				$product_list[$product->category_id]['caption'] = (!empty($category))? $category->title: 'No category';
				$product_list[$product->category_id]['items'][] = [
					'id'		=> $product->id,
					'title'		=> $product->title,
					'price'		=> number_format((float)$product->price, 2, '.', ' ')
				];
			}
			unset($promo->id);

			//Get promo settings
			$settings = Settings::select('options')->where('slug','=','promo')->first()->toArray();
			$settings = json_decode($settings['options']);

			return view('admin.add.promo', [
				'start'			=> $start,
				'page'			=> $request->path(),
				'breadcrumbs'	=> $breadcrumbs,
				'title'			=> 'Edit '.$page->title.' "'.$promo->title.'"',
				'products'		=> $product_list,
				'settings'		=> $settings,
				'content'		=> $promo
			]);
		}
	}


	/**
	 * POST /admin/promo
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function store(Request $request){
		$user = Auth::user();
		//Transform request data into proper
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['images'];

		//If there are products with such link
		$data['slug'] = (Promo::where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		$result = Promo::create([
			'title'			=> $data['title'],
			'slug'			=> $data['slug'],
			'img_url'		=> json_encode($img_url),
			'description'	=> $data['description'],
			'text'			=> $data['text'],
			'date_start'	=> $data['date_start'],
			'date_finish'	=> $data['date_finish'],
			'discount_type'	=> $data['discount_type'],
			'discount'		=> $data['discount'],
			'meta_title'	=> $data['meta_title'],
			'meta_description'=> $data['meta_description'],
			'meta_keywords'	=> $data['meta_keywords'],
			'seo_title'		=> $data['seo_title'],
			'seo_text'		=> $data['seo_text'],
			'enabled'		=> $data['enabled'],
			'created_by'	=> $user['id'],
			'updated_by'	=> $user['id']
		]);

		if(isset($data['product_id']) && ($result != false)){
			foreach($data['product_id'] as $product_id){
				Product::where('id','=',$product_id)->update(['refer_to_promo'=>$result->id]);
			}
		}

		if($result != false){
			return (isset($data['ajax']))
				? json_encode(['message'=>'success'])
				: redirect()->route('admin.promo.index');
		}
	}


	/**
	 * PUT|PATCH /admin/promo/{id}
	 * @param $id Promo ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function update($id, Request $request){
		$user = Auth::user();
		//Transform request data into proper
		$temp = $this->processData($request);
		$data = $temp['data'];
		$img_url = $temp['images'];
		//If there are products with such link
		$data['slug'] = (Promo::where('id','!=',$id)->where('slug','=',$data['slug'])->count() > 0)
			? $data['slug'].'_'.uniqid()
			: $data['slug'];

		Product::where('refer_to_promo','=',$id)->update(['refer_to_promo'=>0]);

		$result = Promo::find($id);
		if(!empty($result)){
			$result->title			= $data['title'];
			$result->slug			= $data['slug'];
			$result->img_url		= json_encode($img_url);
			$result->description	= $data['description'];
			$result->text			= trim($data['text']);
			$result->date_start		= $data['date_start'];
			$result->date_finish	= $data['date_finish'];
			$result->discount_type	= $data['discount_type'];
			$result->discount		= $data['discount'];
			$result->meta_title		= $data['meta_title'];
			$result->meta_description= $data['meta_description'];
			$result->meta_keywords	= $data['meta_keywords'];
			$result->seo_title		= $data['seo_title'];
			$result->seo_text		= $data['seo_text'];
			$result->enabled		= $data['enabled'];
			$result->updated_by		= $user['id'];
			$result->save();

			if(isset($data['product_id']) && ($result != false)){
				foreach($data['product_id'] as $product_id){
					Product::where('id','=',$product_id)->update(['refer_to_promo'=>$result->id]);
				}
			}

			if($result != false){
				return (isset($data['ajax']))
					? json_encode(['message'=>'success'])
					: redirect()->route('admin.promo.index');
			}
		}else{
			return json_encode([
				'message'=> 'error',
				'errors' => ['There is no promo with ID #'.$id]
			]);
		}
	}


	/**
	 * DELETE /admin/promo/{id}
	 * @param $id Promo ID
	 * @return string
	 */
	public function destroy($id){
		Product::where('refer_to_promo','=',$id)->update(['refer_to_promo'=>0]);
		$result = Promo::find($id)->delete();
		if($result != false){
			return json_encode(['message'=>'success']);
		}
	}


	/**
	 * @param \App\Promo $promo
	 * @return \App\Promo $promo
	 */
	public function getPromoData($promo){
		//Create images array
		$images = [];
		if($this->isJson($promo->img_url)){
			$promo->img_url = json_decode($promo->img_url);
			foreach($promo->img_url as $image){
				$name = $this->getFileName($image->src);
				$images[] = [
					'src'		=> $image->src,
					'alt'		=> (isset($image->alt))? $image->alt: '',
					'name'		=> $name,
					'size'		=> self::niceFilesize(base_path().'/public'.$image->src)
				];
			}
		}
		$promo->img_url = $images;

		//Transform date view
		if(!empty($promo->date_start)){
			$promo->date_start = date('d/m/Y', strtotime($promo->date_start));
		}
		if(!empty($promo->date_finish)){
			$promo->date_finish = date('d/m/Y', strtotime($promo->date_finish));
		}

		//Get promotion products
		$promo_products = Product::select('id')->where('refer_to_promo','=',$promo->id)->get();
		$promo_products_list = [];
		foreach($promo_products as $product){
			$promo_products_list[] = $product->id;
		}
		$promo->products = $promo_products_list;
		//Get creator
		$promo->created_by = $promo->createdBy()->select('name','email')->first();
		//Get updater
		$promo->updated_by = $promo->updatedBy()->select('name','email')->first();

		return $promo;
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

		if(isset($data['date_start'])){
			$temp = explode('/',$data['date_start']);
			$data['date_start'] = $temp[2].'-'.$temp[1].'-'.$temp[0].' 00:00:00';
		}

		if(isset($data['date_finish'])){
			$temp = explode('/',$data['date_finish']);
			$data['date_finish'] = $temp[2].'-'.$temp[1].'-'.$temp[0].' 00:00:00';
		}

		//Change checkbox values to boolean
		if(isset($data['ajax'])){
			$data['enabled'] = (isset($data['enabled'])) ? $data['enabled'] : 0;
		}else{
			$data['enabled'] = (isset($data['enabled']) && ($data['enabled'] == 'on')) ? 1 : 0;
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
		return [
			'data' => $data,
			'images' => $img_url
		];
	}
}