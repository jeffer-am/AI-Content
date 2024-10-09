<?php

namespace AIMuseVendor\Illuminate\Contracts\Mail;

interface Factory
{
    /**
     * Get a mailer instance by name.
     *
     * @param  string|null  $name
     * @return \AIMuseVendor\Illuminate\Contracts\Mail\Mailer
     */
    public function mailer($name = null);
}
