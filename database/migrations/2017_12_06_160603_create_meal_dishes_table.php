<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMealDishesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('meal_dishes', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('category_id');
			$table->text('square_img')->nullable();
			$table->text('large_img')->nullable();
			$table->text('img_url')->nullable();
			$table->text('model_3d')->nullable();
			$table->string('price')->nullable();
			$table->double('dish_weight',10,3)->nullable();
			$table->double('calories',10,3)->nullable();
			$table->text('text')->nullable();
			$table->string('cooking_time')->nullable();
			$table->boolean('is_recommended')->unsigned();
			$table->integer('views')->unsigned()->default(0);
			$table->boolean('enabled')->unsigned();
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
		Schema::dropIfExists('meal_dishes');
	}
}
