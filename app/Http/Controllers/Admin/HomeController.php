<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;

use App\Http\Controllers\AppController;
use App\MealDish;
use App\VisitorOrder;
use Illuminate\Http\Request;

class HomeController extends AppController
{
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());
			$page = AdminMenu::select('title')->where('slug', '=', $current_page)->first();
			$breadcrumbs = $this->breadcrumbs($current_page, 'pages');

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

			$orders = VisitorOrder::select('id','visitor_id','restaurant_id','items','status','created_at');
			if($sorting_settings['sort'] == 'created_at'){
				$orders = $orders->orderBy('created_at',$sorting_settings['dir']);
			}
			$orders = $orders->paginate(25);
			$content = [];

			foreach($orders as $order){
				$visitor = $order->visitor()->select('name','surname')->first();

				$restaurant = $order->restaurant()->select('title')->first();

				$items = [];
				foreach($order->items as $dish_id => $quantity){
					$dish = MealDish::select('title','price')->find($dish_id);
					if(!empty($dish)){
						$items[] = [
							'title' => $dish->title,
							'price' => $dish->price,
							'quantity' => $quantity
						];
					}
				}

				if(!empty($restaurant) && !empty($visitor)){
					$content[] = [
						'id'		=> (int)$order->id,
						'visitor'	=> $visitor->name.' '.$visitor->surname,
						'restaurant'=> $restaurant->title,
						'items'		=> $items,
						'created'	=> date('Y-m-d H:i', strtotime($order->created_at))
					];
				}
			}

			switch(true){
				case ($sorting_settings['sort'] == 'name'):
					if($sorting_settings['dir'] == 'desc'){
						usort($content, function($a, $b){
							return $a['visitor'] < $b['visitor'];
						});
					}else{
						usort($content, function($a, $b){
							return $b['visitor'] > $a['visitor'];
						});
					}
				break;
				case ($sorting_settings['sort'] == 'restaurant'):
					if($sorting_settings['dir'] == 'desc'){
						usort($content, function($a, $b){
							return $a['restaurant'] < $b['restaurant'];
						});
					}else{
						usort($content, function($a, $b){
							return $a['restaurant'] > $b['restaurant'];
						});
					}
				break;
				case ($sorting_settings['sort'] ==  'id'):
					if($sorting_settings['dir'] == 'desc'){
						usort($content, function($a, $b){
							return $a['id'] < $b['id'];
						});
					}else{
						usort($content, function($a, $b){
							return $a['id'] > $b['id'];
						});
					}
				break;
			}

			$pagination_options = $this->createPaginationOptions($orders, $sorting_settings);

			return view('admin.home', [
				'start'		=> $start,
				'page'		=> $current_page,
				'breadcrumbs'=> $breadcrumbs,
				'title'		=> 'Заказы',
				'pagination'=> $pagination_options,
				'content'	=> $content
			]);
		}
	}
}

