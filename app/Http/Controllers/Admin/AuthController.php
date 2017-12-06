<?php
namespace App\Http\Controllers\Admin;

use App\User;

use App\Http\Controllers\AppController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use Auth;
use Crypt;
use Validator;

class AuthController extends AppController{
	/**
	 * @return \Illuminate\View\View
	 */
	public function loginPage(){
		return view('admin.login');
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function login(Request $request){
		$data = $request->all();
		$data['email'] = trim($data['email']);

		$user = User::select('id','password','role')->where('email','=',$data['email'])->first();
		//Checking for isset user and his permissions
		if($user == false || empty($user->role)){
			return redirect()->route('home');
		}

		//Compare password hashes
		if(Hash::check($data['password'], $user->password)){
			$auth = Auth::loginUsingId($user->id);
			if(!$auth){
				return redirect()->route('home');
			}
			return redirect()->route('admin.home');
		}
	}
}