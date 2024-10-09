<?php

namespace AIMuse\Wordpress\Hooks\Filters;

/**
 * Filter Hook: Filters the non-default cron schedules.
 *
 * @link https://developer.wordpress.org/reference/hooks/cron_schedules/
 */
class CronSchedulesFilter extends Filter
{
  public function __construct()
  {
    $this->name = 'cron_schedules';
    $this->acceptedArgs = 1;
  }

  public function handle(array $newSchedules): array
  {
    $newSchedules['every_15_minutes'] = array(
      'interval' => 900,
      'display' => __('Every 15 Minutes')
    );

    return $newSchedules;
  }
}
