<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RestorePassword extends Model
{
	protected $table = 'restore_passwords';
	protected $fillable = [
		'user_id','type'
	];
}
