<?php

namespace AIMuseVendor\Illuminate\Contracts\Queue;

interface Factory
{
    /**
     * Resolve a queue connection instance.
     *
     * @param  string|null  $name
     * @return \AIMuseVendor\Illuminate\Contracts\Queue\Queue
     */
    public function connection($name = null);
}
