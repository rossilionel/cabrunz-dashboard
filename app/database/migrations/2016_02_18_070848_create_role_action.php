<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleAction extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('role_action', function(Blueprint $table)
		{

			$table->increments('id');
			$table->integer('role_id')->default(0);
			$table->integer('dash')->default(0);
			$table->integer('map')->default(0);
			$table->integer('prov')->default(0);
			$table->integer('req')->default(0);
			$table->integer('user')->default(0);
			$table->integer('review')->default(0);
			$table->integer('setting')->default(0);
			$table->integer('info')->default(0);
			$table->integer('type')->default(0);
			$table->integer('doc')->default(0);
			$table->integer('promo')->default(0);
			$table->integer('customize')->default(0);
			$table->integer('payment')->default(0);
			$table->integer('actions')->default(0);
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
		Schema::drop('role_action');
	}

}
