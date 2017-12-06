<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryTypes extends Model
{
	protected $table = 'category_types';
	public $fillable = [
		'title','slug','options','enabled','created_by','updated_by'
	];

	public function categories(){
		return $this->hasMany('App\Category','category_type','id');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
