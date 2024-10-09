<?php

namespace AIMuse\Wordpress\Hooks\Actions\Freemius;

use AIMuse\Wordpress\Hooks\Actions\Action;

/**
 * Fires as an admin screen or script is being initialized.

 *
 * @link https://developer.wordpress.org/reference/hooks/admin_init/
 */
class PricingPageCampaignAction extends Action
{
  public function __construct()
  {
    $this->name = "admin_init";
  }

  public function handle()
  {
    if (fs_request_get('page', false) != aimuse()->name() . '-pricing') return;

    wp_enqueue_script('aimuse-pricing-campaign', aimuse()->url('assets/js/campaign-frame.js'), [], aimuse()->version(), true);
  }
}
