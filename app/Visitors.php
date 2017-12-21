<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Visitors extends Model
{
	protected $table = 'visitors';
	protected $fillable = [
		'phone','password','email','name','surname','img_url','status','sms_code'
	];
	protected $hidden = [
		'password'
	];

	public function orders(){
		return $this->hasMany('App\VisitorOrder','visitor_id','id');
	}

	public function likes(){
		return $this->hasMany('App\VisitorsRates', 'visitor_id', 'id');
	}
}
