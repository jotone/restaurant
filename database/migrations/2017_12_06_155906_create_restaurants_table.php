<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('restaurants', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->string('phone',32)->nullable();
			$table->text('logo_img')->nullable();
			$table->text('square_img')->nullable();
			$table->text('large_img')->nullable();
			$table->text('img_url')->nullable();
			$table->text('text')->nullable();
			$table->text('address')->nullable();
			$table->text('work_time')->nullable();
			$table->boolean('has_delivery')->unsigned();
			$table->boolean('has_wifi')->unsigned();
			$table->boolean('has_parking')->unsigned();
			$table->text('coordinates')->nullable();
			$table->text('etc_data')->nullable();
			$table->text('rating')->nullable();
			$table->integer('views')->unsigned()->default(0);
			$table->boolean('enabled')->unsigned();
			$table->text('category_id');
			$table->integer('created_by')->unsigned();
			$table->integer('updated_by')->unsigned();
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
		Schema::dropIfExists('restaurants');
	}
}
