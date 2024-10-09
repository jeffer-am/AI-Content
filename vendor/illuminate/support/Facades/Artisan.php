<?php

namespace AIMuseVendor\Illuminate\Support\Facades;

use AIMuseVendor\Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch queue(string $command, array $parameters = [])
 * @method static \Illuminate\Foundation\Console\ClosureCommand command(string $command, callable $callback)
 * @method static array all()
 * @method static int call(string $command, array $parameters = [], \AIMuseVendor\Symfony\Component\Console\Output\OutputInterface|null $outputBuffer = null)
 * @method static int handle(\AIMuseVendor\Symfony\Component\Console\Input\InputInterface $input, \AIMuseVendor\Symfony\Component\Console\Output\OutputInterface|null $output = null)
 * @method static string output()
 * @method static void terminate(\AIMuseVendor\Symfony\Component\Console\Input\InputInterface $input, int $status)
 *
 * @see \AIMuseVendor\Illuminate\Contracts\Console\Kernel
 */
class Artisan extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConsoleKernelContract::class;
    }
}
