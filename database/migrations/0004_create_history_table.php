<?php

use AIMuseVendor\Illuminate\Database\Migrations\Migration;
use AIMuseVendor\Illuminate\Database\Schema\Blueprint;
use AIMuseVendor\Illuminate\Support\Facades\Schema;

if (!defined('ABSPATH')) exit;
return new class extends Migration
{
  public $feature = 'history';
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aimuse_history', function (Blueprint $table) {
      $table->id();
      $table->bigInteger('user_id')->unsigned();
      $table->string('model');
      $table->string('service');
      $table->string('component');
      $table->integer('tokens');
      $table->decimal('price', 8, 6);
      $table->json('data')->nullable();
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
    Schema::dropIfExists('aimuse_history');
  }
};
