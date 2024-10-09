<?php

if (!defined('ABSPATH')) exit;

use AIMuseVendor\Illuminate\Database\Migrations\Migration;
use AIMuseVendor\Illuminate\Database\Schema\Blueprint;
use AIMuseVendor\Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public $feature = 'datasets';
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aimuse_datasets', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->text('description')->nullable();
      $table->string('slug')->unique();
      $table->boolean('hidden')->default(false);
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
    Schema::dropIfExists('aimuse_datasets');
  }
};
