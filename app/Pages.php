<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pages extends Model
{
	protected $table = 'pages';
	protected $fillable = [
		'title', 'slug',
		'meta_title', 'meta_description', 'meta_keywords',
		'need_seo', 'seo_title', 'seo_text',
		'template_id','enabled','created_by','updated_by'
	];

	public function template(){
		return $this->belongsTo('App\Template');
	}

	public function content(){
		return $this->hasMany('App\PageContent','page_id','id');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
