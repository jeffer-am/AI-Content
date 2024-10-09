<?php

namespace AIMuse\Wordpress\Hooks\Actions\Freemius;

use AIMuse\Wordpress\Hooks\Actions\Action;

/**
 * Action Hook: Fires as an admin screen or script is being initialized.
 *
 * @link https://developer.wordpress.org/reference/hooks/admin_init/
 */
class CheckoutURLAction extends Action
{
  public function __construct()
  {
    $this->name = 'admin_init';
  }

  public function handle()
  {
    $conditions = [
      fs_request_get('page', false) != aimuse()->name() . '-pricing',
      !fs_request_get('checkout', false),
      !!fs_request_get('licenses', false),
    ];

    if (array_filter($conditions)) {
      return;
    }

    $licenses = [
      'premium' => 1,
      'plus' => 10,
      'agency' => 100,
    ];

    $params = array_filter($_GET, function ($key) {
      return $key != 'pricing_id';
    }, ARRAY_FILTER_USE_KEY);

    $params = array_merge($params, [
      'licenses' => $licenses[fs_request_get('plan_name')],
      'hide_licenses' => 'true',
    ]);

    $url = aimuse()->freemius()->checkout_url(fs_request_get('billing_cycle'), false, $params, is_network_admin());

    wp_redirect($url);
  }
}
