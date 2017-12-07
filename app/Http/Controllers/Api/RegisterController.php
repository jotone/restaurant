<?php
namespace App\Http\Controllers\Api;

use App\Visitors;

use Illuminate\Http\Request;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Crypt;

class RegisterController extends ApiController
{
	/**
	 * Create user Account with phone number
	 * @param Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function createAccount(Request $request){
		$phone = $request->input('phone');
		//Drop all chars from phone
		$phone = preg_replace('/\D+/', '', $phone);

		$code = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);

		if(Visitors::where('phone', '=', $phone)->count() > 0){
			$user = Visitors::select('id','status','sms_code')
				->where('phone', '=', $phone)
				->first();

			switch($user->status){
				case '0':
					Visitors::where('id','=',$user->id)->update([
						'sms_code'=>$code
					]);

					/*
					 * SEND SMS TO USER
					 */

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
						'Такой пользователь уже существует.'
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

		/*
		 * SEND SMS TO USER
		 */

		return response(json_encode([
			'step'	=> 1,
			'id'	=> Crypt::encrypt($user->id)
		]), 201);
	}

	/**
	 * SMS Code acknowledge
	 * @param $id
	 * @param Request $request
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
	 */
	public function submitSmsCode($id, Request $request){
		$data = $request->all();
		$id = Crypt::decrypt($id);
		//If there is user with such ID and sms code
		if(Visitors::where('id','=',$id)->where('sms_code','=',$data['sms'])->count() == 1){
			$user = Visitors::find($id);
			$user->status = 1;
			$user->save();

			return response(json_encode([
				'step'	=> 2,
				'id'	=> Crypt::encrypt($user->id)
			]), 201);
		}else{
			return response(json_encode([
				'Код СМС не подтвержден.'
			]), 400);
		}
	}
}