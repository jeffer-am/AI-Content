<?php

namespace AIMuseVendor\Illuminate\Support\Facades;

/**
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder create(string $table, \Closure $callback)
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder createDatabase(string $name)
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder disableForeignKeyConstraints()
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder drop(string $table)
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder dropDatabaseIfExists(string $name)
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder dropIfExists(string $table)
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder enableForeignKeyConstraints()
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder rename(string $from, string $to)
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder table(string $table, \Closure $callback)
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static bool dropColumns(string $table, array $columns)
 * @method static bool hasTable(string $table)
 * @method static void defaultStringLength(int $length)
 * @method static void registerCustomDoctrineType(string $class, string $name, string $type)
 * @method static array getColumnListing(string $table)
 * @method static string getColumnType(string $table, string $column)
 * @method static void morphUsingUuids()
 * @method static \AIMuseVendor\Illuminate\Database\Connection getConnection()
 * @method static \AIMuseVendor\Illuminate\Database\Schema\Builder setConnection(\AIMuseVendor\Illuminate\Database\Connection $connection)
 *
 * @see \AIMuseVendor\Illuminate\Database\Schema\Builder
 */
class Schema extends Facade
{
    /**
     * Get a schema builder instance for a connection.
     *
     * @param  string|null  $name
     * @return \AIMuseVendor\Illuminate\Database\Schema\Builder
     */
    public static function connection($name)
    {
        return static::$app['db']->connection($name)->getSchemaBuilder();
    }

    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \AIMuseVendor\Illuminate\Database\Schema\Builder
     */
    protected static function getFacadeAccessor()
    {
        return static::$app['db']->connection()->getSchemaBuilder();
    }
}
