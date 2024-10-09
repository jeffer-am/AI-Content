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
    Schema::create('aimuse_dataset_conversations', function (Blueprint $table) {
      $table->id();
      $table->integer('dataset_id');
      $table->longText('prompt');
      $table->longText('response');
      $table->integer('priority')->default(1);
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
    Schema::dropIfExists('aimuse_dataset_conversations');
  }
};
