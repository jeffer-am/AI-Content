<?php

namespace AIMuse\Wordpress\Hooks\Actions;

use AIMuse\Database;
use AIMuse\Models\Settings;
use AIMuse\Services\Api\Stream;
use AIMuseVendor\Illuminate\Support\Facades\Log;

class ActivatePluginAction extends Action
{
  public function __construct()
  {
    $this->name = 'activate_' . aimuse()->file();
  }

  public function handle()
  {
    Log::info('Activating plugin');
  }
}
