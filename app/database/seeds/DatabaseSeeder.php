<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();
		
		$this->call('AlterSettingsTableSeeder');

		$this->call('SettingsTableSeeder');

		$this->call('DocumentTableSeed');

		$this->call('TypeTableSeed');

		$this->call('KeywordsTableSeed');

		$this->call('IconsTableSeeder');
	}

}
