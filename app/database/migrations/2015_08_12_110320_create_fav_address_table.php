<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFavAddressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('fav_address', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('owner_id');
			$table->string('address');
			$table->double('latitude', 15, 8);
			$table->double('longitude', 15, 8);
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
		Schema::drop('fav_address');
	}

}
