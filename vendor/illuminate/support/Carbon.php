<?php

namespace AIMuseVendor\Illuminate\Support;

use AIMuseVendor\Carbon\Carbon as BaseCarbon;
use AIMuseVendor\Carbon\CarbonImmutable as BaseCarbonImmutable;

class Carbon extends BaseCarbon
{
    /**
     * {@inheritdoc}
     */
    public static function setTestNow($testNow = null)
    {
        BaseCarbon::setTestNow($testNow);
        BaseCarbonImmutable::setTestNow($testNow);
    }
}
