<?php

namespace AIMuse\Wordpress\Hooks\Filters\Freemius;

use AIMuse\Models\Settings;
use AIMuse\Wordpress\Hooks\Filters\Filter;

class AfterPremiumActivationFilter extends Filter
{
  public function __construct()
  {
    $this->name = 'fs_after_license_change_' . aimuse()->name();
    $this->acceptedArgs = 2;
  }

  public function renderNotification()
  {
    echo '<iframe width="0" height="0" frameborder="0" src="https://wpaimuse.com/thank-you-for-purchasing/" style="position:absolute;width:0;height:0;border:0;"></iframe>';
  }

  public function handle($plan_change, $plan)
  {
    $isReported = Settings::get('isPremiumNotified', false);

    if ($plan_change === 'upgraded' && $isReported === false) {
      add_action('admin_footer', [$this, 'renderNotification']);

      Settings::set('isPremiumNotified', true);
    }
  }
}
