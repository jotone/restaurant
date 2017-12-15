<?php
namespace App\Http\Controllers\Api;

use App\Visitors;

use Illuminate\Http\Request;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Crypt;

class RegisterController extends ApiController
{
	/**
	 * POST /api/create_account
	 * Create user Account with phone number
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function createAccount(Request $request){
		$phone = $request->input('phone');
		//Drop all chars from phone
		$phone = preg_replace('/\D+/', '', $phone);

		//$code = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);
		$code = '1111';

		if(Visitors::where('phone', '=', $phone)->count() > 0){
			$user = Visitors::select('id','status','sms_code')
				->where('phone', '=', $phone)
				->first();

			switch($user->status){
				case '0':
					Visitors::where('id','=',$user->id)->update([
						'sms_code'=>$code
					]);

					$this->sendSMStoUser($phone, $code);

					return response(json_encode([
						'step'	=> 1,
						'id'	=> Crypt::encrypt($user->id)
					]), 201);
				break;
				case '1':
					return response(json_encode([
						'step'	=> 2,
						'id'	=> Crypt::encrypt($user->id)
					]), 201);
				break;
				case '2':
					return response(json_encode([
						'input_error'	=> 1,
						'type'			=> 'phone',
						'message'		=> 'Такой пользователь уже существует.'
					]), 400);
				break;
			}
		}
		//User not isset

		$user = Visitors::create([
			'phone'		=> $phone,
			'status'	=> 0,
			'sms_code'	=> $code
		]);

		$this->sendSMStoUser($phone, $code);

		return response(json_encode([
			'step'	=> 1,
			'id'	=> Crypt::encrypt($user->id)
		]), 201);
	}


	/**
	 * PUT /api/submit_profile/{id}
	 * SMS Code acknowledge
	 * @param $id user ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function submitSmsCode($id, Request $request){
		$data = $request->all();
		$visitor_id = Crypt::decrypt($id);
		$data['sms'] = preg_replace('/\D+/', '', $data['sms']);
		//If there is user with such ID and sms code
		if(Visitors::where('id','=',$visitor_id)->where('sms_code','=',$data['sms'])->count() == 1){
			$user = Visitors::find($visitor_id);
			$user->status = 1;
			$user->save();

			return response(json_encode([
				'step'	=> 2,
				'id'	=> $id
			]), 201);
		}else{
			return response(json_encode([
				'input_error'	=> 1,
				'type'			=> 'sms',
				'message'		=> 'Код СМС не подтвержден.'
			]), 400);
		}
	}


	/**
	 * PUT /api/generate_sms/{id}
	 * @param $id user ID
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function generateSMS($id){
		//$code = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);
		$code = 2222;
		$visitor_id = Crypt::decrypt($id);
		$user = Visitors::find($visitor_id);
		if(empty($user)){
			return response(json_encode([
				'message' => 'Такого пользователя не существует'
			]), 400);
		}

		$user->sms_code = $code;
		$user->save();

		$this->sendSMStoUser($user->phone, $code);

		return response(json_encode([
			'step'	=> 1,
			'id'	=> $id
		]), 201);
	}


	/**
	 * PUT /api/submit_profile/{id}
	 * @param $id user ID
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function submitProfile($id, Request $request){
		$data = $request->all();

		foreach($data as $key => $val){
			$data[$key] = trim($val);
		}

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

		if(Visitors::where('email','=',$data['email'])->count() > 0){
			return response(json_encode([
				'input_error'	=> 1,
				'type'			=> 'email',
				'message'		=> 'Пользователь с данной почтой уже существует'
			]), 400);
		}

		$visitor_id = Crypt::decrypt($id);

		$user = Visitors::find($visitor_id);
		if(empty($user)){
			return response(json_encode([
				'message' => 'Такого пользователя не существует'
			]), 400);
		}

		$img = (!empty($data['img']))
			? $this->createImgBase64($data['img'], true)
			: null;

		$user->name		= $data['name'];
		$user->surname	= $data['surname'];
		$user->email	= $data['email'];
		$user->password	= md5($data['pass']);
		$user->img_url	= $img;
		$user->status	= 2;
		$user->save();

		return response(json_encode([
			'step'	=> 3,
			'id'	=> $id,
			'img'	=> asset($img)
		]), 201);
	}
}