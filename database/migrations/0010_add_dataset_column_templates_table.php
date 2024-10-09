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
    Schema::table('aimuse_templates', function (Blueprint $table) {
      $table->string('dataset_slug')->nullable()->after('enabled');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('aimuse_templates', function (Blueprint $table) {
      $table->dropColumn('dataset_slug');
    });
  }
};
