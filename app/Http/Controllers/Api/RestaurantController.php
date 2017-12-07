<?php
namespace App\Http\Controllers\Api;

use \App\Http\Controllers\ApiController;

class RestaurantController extends ApiController
{
	public function getAll(){
		return json_encode([
			'success'
		]);
	}
}