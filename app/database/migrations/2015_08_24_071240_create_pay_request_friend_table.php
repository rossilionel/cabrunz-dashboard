<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePayRequestFriendTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pay_request_friend', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('request_id');
			$table->integer('owner_id');
			$table->integer('friend_id');
			$table->float('total');
			$table->integer('status');
			$table->string('instruction');
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
		Schema::drop('pay_request_friend');
	}

}
