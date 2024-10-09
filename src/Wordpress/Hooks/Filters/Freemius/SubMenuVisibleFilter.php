<?php

namespace AIMuse\Wordpress\Hooks\Filters\Freemius;

use AIMuse\Helpers\PremiumHelper;
use AIMuse\Wordpress\Hooks\Filters\Filter;

class SubMenuVisibleFilter extends Filter
{
  public function __construct()
  {
    $this->name = 'fs_is_submenu_visible_' . aimuse()->name();
    $this->acceptedArgs = 2;
  }

  public function handle(bool $enabled, string $id): bool
  {
    if ($id === 'pricing') {
      return !PremiumHelper::isPremium();
    }

    return $enabled;
  }
}
