<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('categories', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->text('text')->nullable();
			$table->text('img_url');
			$table->string('meta_title')->nullable();
			$table->text('meta_description')->nullable();
			$table->text('meta_keywords')->nullable();
			$table->boolean('need_seo')->unsigned();
			$table->string('seo_title')->nullable();
			$table->text('seo_text')->nullable();
			$table->integer('views')->unsigned()->default(0);
			$table->boolean('enabled')->unsigned();
			$table->integer('category_type')->unsigned();
			$table->integer('refer_to')->unsigned();
			$table->smallInteger('position')->unsigned();
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
		Schema::dropIfExists('categories');
	}
}
