<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
	protected $table = 'restaurants';
	public $fillable = [
		'title','slug','logo_img','text','img_url','address','work_time',
		'has_delivery','has_wifi','coordinates','etc_data',
		'rating','views','enabled','category_id',
		'created_by','updated_by'
	];

	public function mealMenus(){
		return $this->hasMany('App\MealMenu');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
