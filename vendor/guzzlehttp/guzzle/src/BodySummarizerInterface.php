<?php

namespace AIMuseVendor\GuzzleHttp;

use AIMuseVendor\Psr\Http\Message\MessageInterface;

interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(MessageInterface $message): ?string;
}
