<?php
namespace App\Http\Controllers\Api;

use App\MealDish;
use App\Restaurant;
use App\VisitorOrder;
use App\Visitors;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class VisitorsController extends ApiController
{
	/**
	 * POST /api/create_order
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function createOrder(Request $request){
		$data = $request->all();

		//Check if User Id is empty
		if(!isset($data['user_id'])){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		//Decrypt ID -> get user-data by ID
		$data['user_id'] = Crypt::decrypt($data['user_id']);

		if(Visitors::where('id','=',$data['user_id'])->count() == 0){
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
					$result = VisitorOrder::create([
						'visitor_id'	=> $data['user_id'],
						'restaurant_id'	=> $restaurant_id,
						'items'			=> json_encode($order_list),
						'status'		=> 1
					]);

					if($result != false){
						$this->sendSMStoUser($restaurant->phone, $message);

						return response(json_encode([
							'message' => 'success'
						]), 201);
					}
				}
			}
		}else{
			return response(json_encode([
				'message' => 'Отплавлять нечего'
			]), 400);
		}
	}


	/**
	 * PUT /api/change_data/{id}
	 * @param $id \App\Visitors ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function changeData($id, Request $request){
		if(empty($id)){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		$visitor_id = Crypt::decrypt($id);

		$user = Visitors::find($visitor_id);
		if(empty($user)){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		$data = $request->all();

		if(!empty($data['pass'])){
			if(strlen($data['pass']) < 6){
				return response(json_encode([
					'input_error'	=> 1,
					'type'			=> 'pass',
					'message'		=> 'Пароль должен содержать как минимум 6 символов'
				]), 400);
			}

			if($data['pass'] != $data['confirm']){
				return response(json_encode([
					'input_error'	=> 1,
					'type'			=> 'confirm',
					'message'		=> 'Пароль не подтвержден'
				]), 400);
			}
		}

		if(Visitors::where('id','!=',$visitor_id)->where('email','=',$data['email'])->count() > 0){
			return response(json_encode([
				'input_error'	=> 1,
				'type'			=> 'email',
				'message'		=> 'Пользователь с данной почтой уже существует'
			]), 400);
		}

		$img = (!empty($data['img']))
			? $this->createImgBase64($data['img'], true)
			: null;

		$user->name		= $data['name'];
		$user->surname	= $data['surname'];
		$user->email	= $data['email'];
		if(!empty($img)){
			$user->img_url	= $img;
		}
		if(!empty($data['pass'])) {
			$user->password = md5($data['pass']);
		}
		$user->save();

		return response(json_encode([
			'id'	=> $id,
			'img'	=> (!empty($img))? asset($img): asset($user->img_url)
		]), 201);
	}
}