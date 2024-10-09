<?php

namespace AIMuse\Helpers;

use AIMuse\Models\AIModel;
use AIMuse\Models\Settings;

class PricingHelper
{
  public static function text(array $options)
  {
    $model = AIModel::query()->where('name', $options['model'])->where('service', $options['service'])->first();

    if (!$model) {
      return 0;
    }

    $pricing = $model->pricing;

    if ($pricing) {
      $inputPrice = $options['tokens']['input'] * $pricing['text']['input'];
      $outputPrice = $options['tokens']['output'] * $pricing['text']['output'];
      return $inputPrice + $outputPrice;
    }

    return 0;
  }

  public static function image(array $options)
  {
    $model = AIModel::query()->where('name', $options['model'])->where('service', $options['service'])->first();

    if (!$model) {
      return 0;
    }

    $pricing = $model->pricing;

    if ($pricing) {
      $pricing = collect($pricing['image']);

      $pricing = $pricing->first(function ($item) use ($options) {
        return $item['quality'] == $options['quality'] && $item['resolution'] == $options['resolution'];
      });

      if ($pricing) {
        return $pricing['price'];
      }
    }

    return 0;
  }
}
