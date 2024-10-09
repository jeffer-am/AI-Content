<?php

namespace AIMuseVendor\Illuminate\Database\Events;

use AIMuseVendor\Illuminate\Contracts\Database\Events\MigrationEvent as MigrationEventContract;
use AIMuseVendor\Illuminate\Database\Migrations\Migration;

abstract class MigrationEvent implements MigrationEventContract
{
    /**
     * A migration instance.
     *
     * @var \AIMuseVendor\Illuminate\Database\Migrations\Migration
     */
    public $migration;

    /**
     * The migration method that was called.
     *
     * @var string
     */
    public $method;

    /**
     * Create a new event instance.
     *
     * @param  \AIMuseVendor\Illuminate\Database\Migrations\Migration  $migration
     * @param  string  $method
     * @return void
     */
    public function __construct(Migration $migration, $method)
    {
        $this->method = $method;
        $this->migration = $migration;
    }
}
