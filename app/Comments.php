<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
	protected $table = 'comments';
	public $fillable = [
		'user_id', 'type', 'post_id', 'refer_to_comment', 'text'
	];
	public function user(){
		return $this->belongsTo('App\User');
	}
}
