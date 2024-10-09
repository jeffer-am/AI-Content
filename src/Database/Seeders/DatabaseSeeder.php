<?php

namespace AIMuse\Database\Seeders;

use AIMuseVendor\Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    try {
      $this->call([
        TemplateSeeder::class,
        SettingsSeeder::class,
        DatasetSeeder::class,
      ]);
    } catch (\Exception $e) {
      return false;
    }
  }
}
