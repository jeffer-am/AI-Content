<?php

namespace AIMuse\Wordpress\Schedules;

use AIMuseVendor\Illuminate\Support\Facades\Log;

class LogPruneSchedule extends Schedule
{
  protected $name = 'aimuse_logs_prune';
  protected $interval = 'daily';

  public function __construct()
  {
    $this->timestamp = strtotime('+1 day');
    parent::__construct();
  }

  public function run()
  {
    try {
      $blogId = get_current_blog_id();
      $dir = dirname(aimuse()->logPath());
      $files = glob("$dir/blog-$blogId-*");

      $settings = get_option('aimuse_logs', [
        'days' => 7,
      ]);

      foreach ($files as $file) {
        $fileDate = $this->getDateFromFileName($file);
        if ($fileDate < strtotime("-{$settings['days']} days")) {
          Log::info('Pruning log', ['file' => $file]);
          unlink($file);
        }
      }

      Log::info('Logs pruned successfully');
    } catch (\Throwable $th) {
      Log::error('Failed to prune logs', [
        'error' => $th,
        'trace' => $th->getTrace()
      ]);
    }
  }

  private function getDateFromFileName(string $name)
  {
    $matches = [];
    preg_match('/blog-\d+-(\d{4}-\d{2}-\d{2})/', $name, $matches);
    return $matches[1] ? strtotime($matches[1]) : time();
  }
}
