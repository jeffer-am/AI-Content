<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;
use AIMuseVendor\Illuminate\Support\Facades\File;

/**
 * @method static \AIMuseVendor\Symfony\Component\Finder\SplFileInfo[] getFiles()
 */

class Template extends Model
{
  protected $table = 'aimuse_templates';
  protected $guarded = [];
  protected static $files = [];

  protected $casts = [
    'option' => 'object',
    'capabilities' => 'array',
  ];

  // Clear these attributes from the request
  public static array $excepts = [
    'changed',
    'restorable',
    'dataset',
  ];

  public function category()
  {
    return $this->belongsTo(TemplateCategory::class);
  }

  public function dataset()
  {
    return $this->belongsTo(Dataset::class, 'dataset_slug', 'slug');
  }

  public function getRestorableAttribute(): bool
  {
    foreach (static::getFiles() as $file) {
      if ($file->getBasename() == $this->slug . '.json') {
        return true;
      }
    }

    return false;
  }

  // Get all the template files from plugin directory
  public static function getFiles(): array
  {
    if (static::$files) {
      return static::$files;
    }

    $files = File::allFiles(aimuse()->dir() . 'database/templates');
    $data = [];

    foreach ($files as $file) {
      if ($file->getExtension() != 'json') {
        continue;
      }

      $data[] = $file;
    }

    return $data;
  }

  public function restore(): bool
  {
    foreach (static::getFiles() as $file) {
      if ($file->getBasename() != $this->slug . '.json') {
        continue;
      }

      $template = json_decode($file->getContents(), true);

      if (isset($template['file'])) {
        $template['prompt'] = File::get($file->getPath() . '/' . $template['file']);
      }

      $this->update([
        'prompt' => $template['prompt'],
        'option' => $template['option'] ?? null,
      ]);

      return true;
    }

    return false;
  }

  public static function export(): array
  {
    $templates = static::with('category')->get();
    $templates = $templates->map(function ($template) {
      return [
        'slug' => $template->slug,
        'category' => $template->category->name,
        'enabled' => $template->enabled,
        'prompt' => $template->prompt,
        'option' => $template->option,
        'capabilities' => $template->capabilities,
      ];
    });

    return $templates->toArray();
  }

  public static function import(array $data): void
  {
    foreach ($data as $template) {
      $category = TemplateCategory::query()->firstOrCreate(['name' => $template['category']]);

      static::updateOrCreate(['slug' => $template['slug']], [
        'category_id' => $category->id,
        'prompt' => $template['prompt'] ?? null,
        'option' => $template['option'] ?? null,
        'capabilities' => $template['capabilities'] ?? [],
        'enabled' => $template['enabled'] ?? false,
      ]);
    }
  }
}
