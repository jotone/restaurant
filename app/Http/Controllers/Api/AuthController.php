<?php
namespace App\Http\Controllers\Api;

use App\Visitors;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AuthController extends ApiController
{
	public function login(Request $request){
		$data = $request->all();

		$data['pass'] = md5($data['pass']);

		$user = Visitors::select('id','name','surname','email')
			->where('email','=',$data['email'])
			->where('password','=',$data['pass'])
			->where('status','=',2)
			->first();
		if(empty($user)){
			return response(json_encode([
				'input_error'	=> 1,
				'type'			=> 'login',
				'message'		=> 'Логин или пароль введен неверно.'
			]), 400);
		}
		$user->id = Crypt::encrypt($user->id);
		$user = $user->toArray();

		return json_encode($user);
	}
}