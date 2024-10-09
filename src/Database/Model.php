<?php

namespace AIMuse\Database;

use AIMuseVendor\Illuminate\Support\Str;
use AIMuseVendor\Illuminate\Support\Facades\Date;
use AIMuseVendor\Illuminate\Database\Eloquent\Builder;
use AIMuseVendor\Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin Builder
 */
class Model extends EloquentModel
{
  public function getAttribute($key)
  {
    $value = parent::getAttribute($key);

    if ($value != null) {
      return $value;
    }

    $key = Str::snake($key);

    if (array_key_exists($key, $this->attributes)) {
      return $this->attributes[$key];
    }

    return null;
  }

  public function freshTimestamp()
  {
    return Date::now()->setTimezone('UTC');
  }
}
