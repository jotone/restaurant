<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
	protected $table = 'restaurants';
	public $fillable = [
		'title','slug','logo_img','text','img_url','address','work_time',
		'has_delivery','has_wifi','etc_data',
		'rating','views','enabled','category_id'
	];

	public function mealMenus(){
		return $this->hasMany('App\MealMenu');
	}
}
