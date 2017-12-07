<?php
namespace App\Http\Controllers\Api;

use App\Visitors;

use Illuminate\Http\Request;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Crypt;

class RegisterController extends ApiController
{
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

	public function submitSmsCode(Request $request){
		$data = $request->all();

	}
}