<?php

namespace AIMuseVendor\Illuminate\Contracts\Container;

use Exception;
use AIMuseVendor\Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
