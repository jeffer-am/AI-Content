<?php

namespace AIMuse\Models;

use AIMuse\Database\Model;
use AIMuseVendor\Illuminate\Database\Eloquent\Collection;

class Settings extends Model
{
  protected $table = 'aimuse_settings';
  protected $guarded = [];

  public $timestamps = false;

  protected $casts = [];

  /**
   * Undocumented variable
   *
   * @var \AIMuseVendor\Illuminate\Database\Eloquent\Collection
   */
  private static $cache = null;

  public static $backupKeys = [
    'openAiApiKey',
    'googleAiApiKey',
    'openRouterApiKey',
    'acceptedTerms',
    'isTextBlockActive',
    'isImageBlockActive',
    'textModel',
    'imageModel',
  ];

  public static $labels = [
    'textModel' => 'Text Model',
    'imageModel' => 'Image Model',
  ];

  private static function cache()
  {
    if (static::$cache === null) {
      static::$cache = static::all();
    }

    return static::$cache;
  }

  public static function clearCache()
  {
    static::$cache = null;
  }

  public static function get($name, $default = [])
  {
    try {
      $setting = static::cache()->where('name', $name)->first();
      return $setting ? unserialize($setting->data) : $default;
    } catch (\Exception $e) {
      return $default;
    }
  }

  public static function set($name, $data)
  {
    try {
      $setting = static::cache()->where('name', $name)->first() ?? new static(['name' => $name]);
      $setting->data = serialize($data);
      $setting->save();
      return $setting;
    } catch (\Exception $e) {
      return false;
    }
  }

  public static function default($name, $data)
  {
    $setting = static::get($name, null);
    if ($setting === null) {
      static::set($name, $data);
    }
  }

  public static function prettify(Collection $data)
  {
    $settings = [];
    foreach ($data as $value) {
      $settings[$value->name] = unserialize($value->data);
    }

    return $settings;
  }

  public static function export()
  {
    $settings = static::cache();
    $backup = [];
    foreach ($settings as $setting) {
      if (in_array($setting->name, static::$backupKeys)) {
        $backup[$setting->name] = unserialize($setting->data);
      }
    }

    return $backup;
  }

  public static function import(array $data)
  {
    foreach ($data as $name => $value) {
      static::set($name, $value);
    }
  }
}
