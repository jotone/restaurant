<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealDish extends Model
{
	protected $table = 'meal_dishes';
	public $fillable = [
		'title','slug','category_id','img_url','model_3d',
		'price','dish_weight','calories','ingredients','cooking_time','is_recommended',
		'views', 'enabled'
	];

	public function category(){
		return $this->belongsTo('App\Category');
	}
}
