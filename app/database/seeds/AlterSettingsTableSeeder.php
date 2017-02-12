<?php

// Composer: "fzaninotto/faker": "v1.3.0"
use Faker\Factory as Faker;

class AlterSettingsTableSeeder extends Seeder {

	public function run()
	{ 
		DB::statement("UPDATE `settings` SET `page` = '1' WHERE `settings`.`id` =43;");
		DB::statement("UPDATE `settings` SET `page` = '1' WHERE `settings`.`id` =42;");
	}

}
