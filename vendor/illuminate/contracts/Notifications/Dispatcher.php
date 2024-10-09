<?php

namespace AIMuseVendor\Illuminate\Contracts\Notifications;

interface Dispatcher
{
    /**
     * Send the given notification to the given notifiable entities.
     *
     * @param  \AIMuseVendor\Illuminate\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function send($notifiables, $notification);

    /**
     * Send the given notification immediately.
     *
     * @param  \AIMuseVendor\Illuminate\Support\Collection|array|mixed  $notifiables
     * @param  mixed  $notification
     * @return void
     */
    public function sendNow($notifiables, $notification);
}
