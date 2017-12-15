<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVisitorOrdersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('visitor_orders', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('visitor_id')->unsigned();
			$table->integer('restaurant_id')->unsigned();
			$table->text('items');
			$table->tinyInteger('status')->unsigned();
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
		Schema::dropIfExists('visitor_orders');
	}
}
