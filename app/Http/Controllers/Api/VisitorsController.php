<?php
namespace App\Http\Controllers\Api;

use App\MealDish;
use App\MealMenu;
use App\Restaurant;

use App\Http\Controllers\ApiController;
use App\Visitors;
use Illuminate\Http\Request;

class VisitorsController extends ApiController
{
	public function createOrder(Request $request){
		$data = $request->all();

		if(!isset($data['user_id'])){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		$user = Visitors::find($data['user_id']);
		if(empty($user)){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		if(isset($data['order'])){
			if($this->isJson($data['order'])){
				$data['order'] = json_decode($data['order'], true);
			}

			foreach($data['order'] as $restaurant_id => $order_list){
				$message = '';
				//Get dishes for current restaurant
				foreach($order_list as $dish_id => $quantity){
					$dish = MealDish::select('title')
						->where('enabled','=',1)
						->where('id','=',$dish_id)
						->first();
					if(!empty($dish)){
						$message .= $dish->title.'. Кол-во: '.$quantity."\r\n";
					}
				}
				//Get restaurant phone number
				$restaurant = Restaurant::select('phone')
					->where('enabled','=',1)
					->where('id','=',$restaurant_id)
					->first();
				//Send SMS
				if(!empty($restaurant)){
					$this->sendSMStoUser($restaurant->phone, $message);

					return response(json_encode([
						'message' => 'success'
					]), 201);
				}
			}
		}else{
			return response(json_encode([
				'message' => 'Отплавлять нечего'
			]), 400);
		}
	}
}