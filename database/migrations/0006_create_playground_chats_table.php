<?php

if (!defined('ABSPATH')) exit;

use AIMuseVendor\Illuminate\Database\Migrations\Migration;
use AIMuseVendor\Illuminate\Database\Schema\Blueprint;
use AIMuseVendor\Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public $feature = 'playground';
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aimuse_playground_chats', function (Blueprint $table) {
      $table->id();
      $table->integer('user_id');
      $table->string('title');
      $table->json('meta')->nullable();
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
    Schema::dropIfExists('aimuse_playground_chats');
  }
};
