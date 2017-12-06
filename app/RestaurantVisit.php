<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RestaurantVisit extends Model
{
	protected $table = 'restaurant_visits';
	public $fillable = [
		'user_id','restaurant_id','meal_list','calories','check_summary','visit_date'
	];

	public function user(){
		return $this->belongsTo('App\Visitors');
	}

	public function restaurant(){
		return $this->belongsTo('App\Restaurant');
	}
}
