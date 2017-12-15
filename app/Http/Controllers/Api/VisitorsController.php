<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class VisitorsController extends ApiController
{
	public function createOrder(Request $request){
		$data = $request->all();
		var_dump($data);
	}
}