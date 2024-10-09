<?php

use AIMuseVendor\Illuminate\Database\Migrations\Migration;
use AIMuseVendor\Illuminate\Database\Schema\Blueprint;
use AIMuseVendor\Illuminate\Support\Facades\Schema;

if (!defined('ABSPATH')) exit;

return new class extends Migration
{
  public $feature = 'settings';
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aimuse_settings', function (Blueprint $table) {
      $table->id();
      $table->string('name')->unique();
      $table->longText('data');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('aimuse_settings');
  }
};
