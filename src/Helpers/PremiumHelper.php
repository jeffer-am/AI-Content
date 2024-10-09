<?php

namespace AIMuse\Helpers;

use AIMuse\Models\AIModel;

class PremiumHelper
{
  public static array $premiumServices = [
    'googleai',
    'openrouter',
  ];

  public static array $freePostTypes = [
    'post',
    'page',
  ];

  public static function getPremiumApiKeyNames()
  {
    return array_map(function ($service) {
      return AIModel::$keyNames[$service];
    }, self::$premiumServices);
  }

  public static function serviceIsPremium(string $service)
  {
    return in_array($service, self::$premiumServices);
  }

  public static function isPremium()
  {
    return !aimuse()->freemius()->is_free_plan();
  }

  public static function toArray()
  {
    $freemius = aimuse()->freemius();
    $premium = array(
      'is_free' => $freemius->is_free_plan(),
      'is_white_labeled' => $freemius->is_whitelabeled(),
    );

    return $premium;
  }
}
