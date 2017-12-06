<?php
namespace App\Http\Controllers\Admin;

use App\AdminMenu;

use App\Http\Controllers\AppController;
use Illuminate\Http\Request;

class HomeController extends AppController
{
	public function index(Request $request){
		$start = $this->getMicrotime();
		$allow_access = $this->checkAccessToPage($request->path());
		if($allow_access === true){
			$current_page = $this->getNaturalPath($request->path());

			return view('admin.home', [
				'start'	=> $start,
				'page'	=> $current_page
			]);
		}
	}
}

