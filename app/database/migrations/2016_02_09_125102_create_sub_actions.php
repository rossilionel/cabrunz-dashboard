<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubActions extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sub_action', function(Blueprint $table)
		{

			$table->increments('id');
			$table->string('provider');
			$table->string('request');
			$table->string('user');
			$table->string('review');
			$table->string('promo');
			$table->string('payment');
			$table->string('admin_id');
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
		Schema::drop('sub_action');
	}

}
