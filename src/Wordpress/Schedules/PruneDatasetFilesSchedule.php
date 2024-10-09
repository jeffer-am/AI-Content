<?php

namespace AIMuse\Wordpress\Schedules;

use AIMuseVendor\Illuminate\Support\Facades\File;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class PruneDatasetFilesSchedule extends Schedule
{
  protected $name = 'aimuse_dataset_files_prune';
  protected $interval = 'daily';

  public function __construct()
  {
    $this->timestamp = strtotime('+1 day');
    parent::__construct();
  }

  public function run()
  {
    try {
      $datasetsDir = WP_CONTENT_DIR . "/uploads/aimuse/datasets";
      if (!File::exists($datasetsDir)) return;

      $files = File::files($datasetsDir);

      foreach ($files as $file) {
        $time = File::lastModified($file);
        $diff = time() - $time;

        if ($diff > 86400) {
          File::delete($file);
          Log::info('Pruned dataset file', [
            'file' => $file,
            'time' => $time,
            'diff' => $diff
          ]);
        }
      }
    } catch (\Throwable $th) {
      Log::error('Failed to prune datasets', [
        'error' => $th,
        'trace' => $th->getTrace()
      ]);
    }
  }
}
