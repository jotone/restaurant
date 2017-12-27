<?php
namespace App\Http\Controllers\Api;

use App\Comments;
use App\MealDish;
use App\Restaurant;
use App\VisitorOrder;
use App\Visitors;

use App\Http\Controllers\ApiController;
use App\VisitorsRates;
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

				//Send SMS to restaurant admin
				if(!empty($restaurant)){
					$result = VisitorOrder::create([
						'visitor_id'	=> $data['user_id'],
						'restaurant_id'	=> $restaurant_id,
						'items'			=> json_encode($order_list),
						'status'		=> 1
					]);

					if($result != false){
						$this->sendSMStoUser($restaurant->phone, $message);
					}
				}
			}
			return response(json_encode([
				'message' => 'success'
			]), 201);
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
		//Decrypt ID
		$visitor_id = Crypt::decrypt($id);
		//Get user's data
		$user = Visitors::find($visitor_id);
		if(empty($user)){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		$data = $request->all();
		$data['pass'] = isset($data['pass'])? trim($data['pass']): null;
		//If password field is empty -> ignore changes
		if(isset($data['pass']) && !empty($data['pass'])){
			//Password must have greater than 6 chars
			if(strlen($data['pass']) < 6){
				return response(json_encode([
					'input_error'	=> 1,
					'type'			=> 'pass',
					'message'		=> 'Пароль должен содержать как минимум 6 символов'
				]), 400);
			}
			//Password must be equal to its confirmation
			if($data['pass'] != $data['confirm']){
				return response(json_encode([
					'input_error'	=> 1,
					'type'			=> 'confirm',
					'message'		=> 'Пароль не подтвержден'
				]), 400);
			}
		}

		//If there is user with such email
		if(Visitors::where('id','!=',$visitor_id)->where('email','=',$data['email'])->count() > 0){
			return response(json_encode([
				'input_error'	=> 1,
				'type'			=> 'email',
				'message'		=> 'Пользователь с данной почтой уже существует'
			]), 400);
		}

		$img = null;

		//If image was really updated
		if(!empty($data['img'])){
			if(!filter_var($data['img'], FILTER_VALIDATE_URL)) {
				$img = $this->createImgBase64($data['img'], true);
			}
		}

		//Save user's data
		$user->name		= $data['name'];
		$user->surname	= $data['surname'];
		$user->email	= $data['email'];
		$user->phone	= $data['phone'];
		if(!empty($img)){
			$user->img_url	= $img;
		}
		if(!empty($data['pass'])) {
			$user->password = md5($data['pass']);
		}
		$user->save();
		//Get updated user's data
		$user = Visitors::select('id','name','surname','email','phone','img_url')->find($visitor_id)->toArray();

		$user['id'] = $id;
		$user['img_url'] = (!empty($img))? asset($img): asset($user['img_url']);

		return response(json_encode($user), 201);
	}


	/**
	 * GET|HEAD /api/get_visits/{user_id}
	 * @param $user_id \App\User ID
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|string|\Symfony\Component\HttpFoundation\Response
	 */
	public function getAll($user_id){
		if(empty($user_id)){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		$visitor_id = Crypt::decrypt($user_id);

		if(Visitors::where('id','=',$visitor_id)->count() == 0){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		//Get visits by date
		$visits = VisitorOrder::select('restaurant_id','items','created_at')
			->where('visitor_id','=',$visitor_id)
			->orderBy('created_at','desc')
			->get();

		//Create result array
		$content = [];
		foreach($visits as $visit){
			//Get restaurant data
			$restaurant = $visit->restaurant()->select('id','title','square_img')->first();

			if(!empty($restaurant)){
				$restaurant = $restaurant->toArray();

				//Create visit default values
				$restaurant['price'] = 0;
				$restaurant['calories'] = 0;
				$restaurant['date'] = date('d.m.Y', strtotime($visit->created_at));

				//Calculate price and calories values
				foreach($visit->items as $dish_id => $quantity){
					$dish = MealDish::select('price','calories')->find($dish_id);
					$restaurant['price'] += $dish->price * $quantity;
					$restaurant['calories'] += $dish->calories * $quantity;
				}

				//Get square image
				$restaurant['square_img'] = json_decode($restaurant['square_img'], true);
				$restaurant['square_img'] = (!empty($restaurant['square_img']['src']))? asset($restaurant['square_img']['src']): '';

				$content[] = $restaurant;
			}
		}

		return json_encode($content);
	}


	/**
	 * GET|HEAD /api/get_visit/{date}/restaurant/{rest_id}/user/{user_id}
	 * @param $date
	 * @param $rest_id \App\Restaurant ID
	 * @param $user_id \App\Visitors ID
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|string|\Symfony\Component\HttpFoundation\Response
	 */
	public function getByDate($date, $rest_id, $user_id){
		if(empty($user_id) || empty($date)){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		$visitor_id = Crypt::decrypt($user_id);

		if(Visitors::where('id','=',$visitor_id)->count() == 0){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}

		$date = date('Y-m-d', strtotime($date));

		//Get visits by date
		$visits = VisitorOrder::select('restaurant_id','items','created_at')
			->where('visitor_id','=',$visitor_id)
			->where('restaurant_id','=',$rest_id)
			->where('created_at','LIKE','%'.$date.'%')
			->get();

		$content = [];
		$items = [];
		foreach($visits as $visit){
			$restaurant = $visit->restaurant()->select('id','title','logo_img','large_img')->first();

			if(!empty($restaurant) && !isset($content['title'])){
				//Create visit default values
				$content['title'] = $restaurant->title;
				$content['price'] = 0;
				$content['calories'] = 0;
				$content['date'] = date('d.m.Y', strtotime($visit->created_at));

				//Get logo image
				$content['logo_img'] = json_decode($restaurant->logo_img);
				$content['logo_img'] = (!empty($restaurant->logo_img->src))? asset($restaurant->logo_img->src): '';

				//Get logo image
				$content['large_img'] = json_decode($restaurant->large_img, true);
				$content['large_img'] = (!empty($restaurant->large_img->src))? asset($restaurant->large_img->src): '';
			}

			if(!empty($restaurant)){
				//Calculate price and calories values
				foreach($visit->items as $dish_id => $quantity){
					$dish = MealDish::select('title','price','calories','dish_weight','model_3d','square_img')->find($dish_id);
					$content['price'] += $dish->price * $quantity;
					$content['calories'] += $dish->calories * $quantity;

					//Get logo image
					$square_img = (!empty($dish->square_img['src']))? asset($dish->square_img['src']): '';

					$dish = $dish->toArray();
					$dish['price'] = (float)$dish['price'];
					$dish['dish_weight'] *= 1000;
					$dish['square_img'] = $square_img;
					$dish['quantity'] = $quantity;
					$items[] = $dish;
				}
			}
		}
		usort($items, function($a, $b){
			return $a['price'] > $b['price'];
		});

		$content['items'] = $items;

		return json_encode($content);
	}


	/**
	 * POST /api/add_comment
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function createComment(Request $request){
		$data = $request->all();
		if(!isset($data['id'])){
			return response(json_encode([
				'Такого пользователя не существует'
			]), 400);
		}
		if(!isset($data['rest_id'])){
			return response(json_encode([
				'Данные не соответствуют параметрам ввода'
			]), 400);
		}

		if(!isset($data['text'])){
			return response(json_encode([
				'Текст отзыва отсутствует'
			]), 400);
		}

		$visitor_id = Crypt::decrypt($data['id']);

		if(
			(Visitors::where('id','=',$visitor_id)->count() == 0) ||
			(Restaurant::where('id','=',$data['rest_id'])->count() == 0)
		){
			return response(json_encode([
				'Данные не соответствуют параметрам ввода'
			]), 400);
		}

		$mark = (isset($data['mark']))? $data['mark']: 0;

		Comments::create([
			'user_id'	=> $visitor_id,
			'type'		=> $mark,
			'post_id'	=> $data['rest_id'],
			'refer_to_comment' => 0,
			'text'		=> trim($data['text'])
		]);

		VisitorsRates::create([
			'visitor_id'	=> $visitor_id,
			'restaurant_id'	=> $data['rest_id'],
			'rating'		=> $mark
		]);

		$restaurant = Restaurant::find($data['rest_id']);
		$rating = $restaurant->rating;
		if($mark > 0){
			$rating->p += 1;
		}
		if($mark < 0){
			$rating->n += 1;
		}
		$restaurant->rating = json_encode($rating);
		$restaurant->save();

		return response(json_encode([
			'message' => 'success'
		]), 201);
	}
}