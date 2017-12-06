<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitorsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('visitors', function (Blueprint $table) {
			$table->increments('id');
			$table->string('phone',32)->unique();
			$table->string('password')->nullable();

			$table->string('email')->nullable();
			$table->string('name')->nullable();
			$table->string('surname')->nullable();
			$table->string('img_url')->nullable();

			$table->tinyInteger('status')->unsigned();
			$table->string('sms_code',4)->nullable();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('visitors');
	}
}
