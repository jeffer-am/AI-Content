<?php

namespace AIMuseVendor\Illuminate\Contracts\Support;

interface MessageProvider
{
    /**
     * Get the messages for the instance.
     *
     * @return \AIMuseVendor\Illuminate\Contracts\Support\MessageBag
     */
    public function getMessageBag();
}
