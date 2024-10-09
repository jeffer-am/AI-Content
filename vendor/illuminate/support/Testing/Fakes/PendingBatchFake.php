<?php

namespace AIMuseVendor\Illuminate\Support\Testing\Fakes;

use Illuminate\Bus\PendingBatch;
use AIMuseVendor\Illuminate\Support\Collection;

class PendingBatchFake extends PendingBatch
{
    /**
     * The fake bus instance.
     *
     * @var \AIMuseVendor\Illuminate\Support\Testing\Fakes\BusFake
     */
    protected $bus;

    /**
     * Create a new pending batch instance.
     *
     * @param  \AIMuseVendor\Illuminate\Support\Testing\Fakes\BusFake  $bus
     * @param  \AIMuseVendor\Illuminate\Support\Collection  $jobs
     * @return void
     */
    public function __construct(BusFake $bus, Collection $jobs)
    {
        $this->bus = $bus;
        $this->jobs = $jobs;
    }

    /**
     * Dispatch the batch.
     *
     * @return \Illuminate\Bus\Batch
     */
    public function dispatch()
    {
        return $this->bus->recordPendingBatch($this);
    }
}
