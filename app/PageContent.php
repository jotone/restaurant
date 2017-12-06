<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
	protected $table = 'page_contents';
	protected $fillable = [
		'type','meta_key','meta_val','page_id'
	];

	public function page(){
		return $this->balongsTo('App\Pages','page_id','id');
	}
}
