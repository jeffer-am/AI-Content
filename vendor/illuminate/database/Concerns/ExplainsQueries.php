<?php

namespace AIMuseVendor\Illuminate\Database\Concerns;

use AIMuseVendor\Illuminate\Support\Collection;

trait ExplainsQueries
{
    /**
     * Explains the query.
     *
     * @return \AIMuseVendor\Illuminate\Support\Collection
     */
    public function explain()
    {
        $sql = $this->toSql();

        $bindings = $this->getBindings();

        $explanation = $this->getConnection()->select('EXPLAIN '.$sql, $bindings);

        return new Collection($explanation);
    }
}
