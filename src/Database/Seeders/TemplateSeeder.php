<?php

namespace AIMuse\Database\Seeders;

use AIMuse\Models\Template;
use AIMuse\Models\TemplateCategory;
use AIMuseVendor\Illuminate\Database\Seeder;
use AIMuseVendor\Illuminate\Support\Facades\File;

class TemplateSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $files = Template::getFiles();

    foreach ($files as $file) {
      $template = json_decode($file->getContents(), true);
      $categoryName = $template['category'] ?? 'Default';

      if (isset($template['file'])) {
        $template['prompt'] = File::get($file->getPath() . '/' . $template['file']);
      }

      $category = TemplateCategory::query()->firstOrCreate([
        'name' => $categoryName,
      ], [
        'name' => $categoryName,
      ]);

      $category->templates()->firstOrCreate([
        'slug' => $template['slug'],
      ], [
        'name' => $template['name'],
        'type' => $template['type'],
        'description' => $template['description'],
        'prompt' => $template['prompt'],
        'option' => $template['option'] ?? null,
        'capabilities' => $template['capabilities'] ?? [],
      ]);
    }
  }
}
