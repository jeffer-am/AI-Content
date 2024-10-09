<?php

namespace AIMuse\Database\Seeders;

use AIMuse\Models\Settings;
use AIMuseVendor\Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    Settings::default('isTextBlockActive', true);
    Settings::default('isImageBlockActive', true);
  }
}
