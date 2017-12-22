<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitorsRates extends Model
{
	protected $table = 'visitors_rates';
	protected $fillable = ['visitor_id', 'restaurant_id', 'rating'];

	public function visitor(){
		return $this->belongsTo('App\Visitors', 'visitor_id', 'id');
	}

	public function restaurant(){
		return $this->belongsTo('App\Restaurant', 'restaurant_id', 'id');
	}
}
