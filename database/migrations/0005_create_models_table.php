<?php

if (!defined('ABSPATH')) exit;

use AIMuseVendor\Illuminate\Database\Migrations\Migration;
use AIMuseVendor\Illuminate\Database\Schema\Blueprint;
use AIMuseVendor\Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public $feature = 'models';
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aimuse_models', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->string('name');
      $table->string('service');
      $table->string('type');
      $table->longText('pricing');
      $table->longText('settings');
      $table->longText('defaults');
      $table->boolean('custom')->default(false);
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('aimuse_models');
  }
};
