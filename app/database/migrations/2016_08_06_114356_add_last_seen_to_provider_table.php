<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
  
 class AddLastSeenToProviderTable extends Migration {
  

  	public function up()
  	{
 		Schema::table('walker', function(Blueprint $table)

  		{
 			$table->integer('last_seen')->default(1453606640);
		
  		});
  	}
  
  	public function down()
  	{
  		Schema::table('walker', function(Blueprint $table)

  		{
    		$table->dropColumn('last_seen');

  		});
  	}
}