<?php
namespace App\Http\Middleware;

use App\Roles;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAdmin{
	public function handle($request, Closure $next, $guard = 'admin'){
		if (!Auth::check()) {
			return redirect(route('admin.login.view'));
		}else{
			$user = Auth::user();
			$roles = Roles::select('slug')->where('access_pages','!=','')->get();
			$role_arr = [];
			foreach($roles as $role){
				$role_arr[] = $role->slug;
			}
			if(!in_array($user['role'],$role_arr)){
				return redirect(route('admin.login.view'));
			}
		}
		return $next($request);
	}
}