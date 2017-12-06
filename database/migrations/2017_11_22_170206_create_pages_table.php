<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pages', function (Blueprint $table) {
			$table->increments('id');
			$table->string('title');
			$table->string('slug');
			$table->string('meta_title')->nullable();
			$table->text('meta_description')->nullable();
			$table->text('meta_keywords')->nullable();
			$table->boolean('need_seo')->unsigned();
			$table->string('seo_title')->nullable();
			$table->text('seo_text')->nullable();
			$table->integer('template_id')->unsigned()->default(0);
			$table->integer('created_by')->unsigned();
			$table->integer('updated_by')->unsigned();
			$table->boolean('enabled')->unsigned();
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
		Schema::dropIfExists('pages');
	}
}
