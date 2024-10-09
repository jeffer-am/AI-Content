<?php

namespace AIMuse\Database\Seeders;

use AIMuse\Models\Dataset;
use AIMuse\Models\DatasetConversation;
use AIMuseVendor\Illuminate\Database\Seeder;
use AIMuseVendor\Illuminate\Support\Facades\File;

class DatasetSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $files = Dataset::getFiles();

    foreach ($files as $file) {
      $dataset = json_decode($file->getContents(), true);

      $created = Dataset::query()->firstOrCreate([
        'slug' => $dataset['slug'],
      ], [
        'name' => $dataset['name'],
        'description' => $dataset['description'],
        'hidden' => $dataset['hidden'] ?? false,
      ]);

      if (isset($dataset['file'])) {
        $content = File::get($file->getPath() . '/' . $dataset['file']);
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) continue;
          $conversation = DatasetConversation::fromJsonLine($line);
          $created->conversations()->save($conversation);
        }
      }
    }
  }
}
