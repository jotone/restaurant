<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealMenu extends Model
{
	protected $table = 'meal_menus';
	public $fillable = [
		'title','slug','restaurant_id','dishes','enabled','category_id'
	];

	public function restaurant(){
		return $this->belongsTo('App\Restaurant');
	}
}
