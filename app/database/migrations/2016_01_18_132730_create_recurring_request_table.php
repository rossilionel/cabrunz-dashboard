<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringRequestTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recurring_request', function(Blueprint $table)
		{

			
			$table->increments('id');
			$table->string('day');
			$table->integer('owner_id');
			$table->string('latitude');
			$table->string('longitude');
			$table->integer('type');
			$table->string('instruction');
			$table->integer('payment_mode');
			$table->string('source_address');
			$table->string('d_latitude');
			$table->string('d_longitude');
			$table->string('destination_address');
			$table->string('distance');
			$table->time('start');
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
		Schema::drop('recurring_request');

	}

}
