<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
	protected $table = 'categories';
	public $fillable = [
		'uniq_id', 'title', 'slug', 'text', 'img_url',
		'meta_title', 'meta_description', 'meta_keywords',
		'need_seo', 'seo_title', 'seo_text',
		'views', 'enabled', 'category_type', 'refer_to', 'position',
		'created_by','updated_by'
	];

	public function category_type(){
		return $this->belongsTo('App\CategoryTypes','category_type','id');
	}

	public function createdBy(){
		return $this->belongsTo('App\User','created_by','id');
	}

	public function updatedBy(){
		return $this->belongsTo('App\User','updated_by','id');
	}
}
