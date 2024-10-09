<?php

use AIMuseVendor\Illuminate\Database\Migrations\Migration;
use AIMuseVendor\Illuminate\Database\Schema\Blueprint;
use AIMuseVendor\Illuminate\Support\Facades\Schema;

if (!defined('ABSPATH')) exit;
return new class extends Migration
{
  public $feature = 'templates';
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('aimuse_templates', function (Blueprint $table) {
      $table->id();
      $table->string('slug')->unique();
      $table->integer('category_id')->unsigned();
      $table->string('type')->default('text');
      $table->string('name');
      $table->string('description')->nullable();
      $table->boolean('enabled')->default(true);
      $table->json('option')->nullable();
      $table->longText('prompt');
      $table->tinyInteger('index')->default(0);
      $table->json('capabilities')->nullable();
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
    Schema::dropIfExists('aimuse_templates');
  }
};
