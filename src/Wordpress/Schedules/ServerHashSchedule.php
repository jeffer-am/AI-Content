<?php

namespace AIMuse\Wordpress\Schedules;

use AIMuse\Models\Settings;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class ServerHashSchedule extends Schedule
{
  protected $name = 'aimuse_server_hash';
  protected $interval = 'every_15_minutes';
  protected $immediate = true;

  public function __construct()
  {
    $this->timestamp = strtotime('+15 minutes');
    parent::__construct();
  }

  public function run()
  {
    $oldHash = Settings::get('serverHash', null);

    $params = [
      get_site_url(),
      sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])),
      sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])),
      phpversion(),
    ];

    $newHash = hash('sha1', implode('#', $params));

    if ($oldHash != $newHash) {
      Settings::set('serverHash', $newHash);
      Settings::set('isStreamChecked', false);
      Log::info('Server hash has changed.', [
        'old' => $oldHash,
        'new' => $newHash,
        'params' => $params,
      ]);
    }
  }
}
