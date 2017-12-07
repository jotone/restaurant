<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MealMenu extends Model
{
	protected $table = 'meal_menus';
	public $fillable = [
		'title','slug','restaurant_id','dishes','enabled','category_id',
		'created_by','updated_by'
	];

	public function restaurant(){
		return $this->belongsTo('App\Restaurant');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
