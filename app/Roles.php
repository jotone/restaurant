<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
	protected $table = 'user_roles';
	public $fillable = [
		'title', 'slug', 'editable', 'access_pages',
		'created_by','updated_by'
	];

	public function users(){
		return $this->hasMany('App\User','role','slug');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
