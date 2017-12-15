<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitorOrder extends Model
{
	protected $table = 'visitor_orders';
	protected $fillable = [
		'visitor_id','restaurant_id','items','status'
	];

	public function getItemsAttribute($value){
		return json_decode($value, true);
	}

	public function visitor(){
		return $this->belongsTo('App\Visitors', 'visitor_id', 'id');
	}

	public function restaurant(){
		return $this->belongsTo('App\Restaurant', 'restaurant_id', 'id');
	}
}
