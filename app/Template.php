<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
	protected $table = 'templates';
	public $fillable = [
		'title','slug','html_content','enabled',
		'created_by','updated_by'
	];

	public function pages(){
		return $this->hasMany('App\Pages');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
