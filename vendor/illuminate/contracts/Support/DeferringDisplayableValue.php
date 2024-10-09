<?php

namespace AIMuseVendor\Illuminate\Contracts\Support;

interface DeferringDisplayableValue
{
    /**
     * Resolve the displayable value that the class is deferring.
     *
     * @return \AIMuseVendor\Illuminate\Contracts\Support\Htmlable|string
     */
    public function resolveDisplayableValue();
}
