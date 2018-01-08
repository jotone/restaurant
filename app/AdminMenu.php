<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
	protected $table = 'admin_menus';
	protected $fillable = [
		'title','slug','img','refer_to','position','enabled'
	];
}
