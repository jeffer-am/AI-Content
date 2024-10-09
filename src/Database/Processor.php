<?php

namespace AIMuse\Database;

use AIMuseVendor\Illuminate\Database\Query\Builder;
use AIMuseVendor\Illuminate\Database\Query\Processors\Processor as BaseProcessor;

class Processor extends BaseProcessor
{
  /**
   * Process an  "insert get ID" query.
   *
   * @param  \AIMuseVendor\Illuminate\Database\Query\Builder  $query
   * @param  string  $sql
   * @param  array  $values
   * @param  string|null  $sequence
   * @return int
   */
  public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
  {
    $query->getConnection()->insert($sql, $values);

    $id = $query->getConnection()->getPdo()->insert_id;

    return is_numeric($id) ? (int) $id : $id;
  }
}
