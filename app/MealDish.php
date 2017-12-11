<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealDish extends Model
{
	protected $table = 'meal_dishes';
	public $fillable = [
		'title','slug','category_id','square_img','large_img','img_url','model_3d',
		'price','dish_weight','calories','text','cooking_time','is_recommended',
		'views','enabled','created_by','updated_by'
	];

	public function getSquareImgAttribute($value){
		return json_decode($value);
	}

	public function getLargeImgAttribute($value){
		return json_decode($value);
	}

	public function getCategoryIdAttribute($value){
		return json_decode($value);
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
