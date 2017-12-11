<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
	protected $table = 'restaurants';
	public $fillable = [
		'title','slug','logo_img','square_img','large_img','img_url','text','address','work_time',
		'has_delivery','has_wifi','coordinates','etc_data',
		'rating','views','enabled','category_id',
		'created_by','updated_by'
	];

	public function getRatingAttribute($value){
		return json_decode($value, true);
	}

	public function getCoordinatesAttribute($value){
		return json_decode($value, true);
	}

	public function mealMenus(){
		return $this->hasMany('App\MealMenu','restaurant_id','id');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
