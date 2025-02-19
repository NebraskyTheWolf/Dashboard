<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\PusherPushNotifications\PusherChannel;
use NotificationChannels\PusherPushNotifications\PusherMessage;

/**
 * Class ShopReportError
 *
 * This class represents a notification when there is an error generating the shop report.
 */
class ShopReportError extends Notification
{

    public function via(object $notifiable): array
    {
        return [PusherChannel::class];
    }

    /**
     * Pushes a notification using PusherMessage.
     *
     * @param mixed $notifiable The recipient of the notification.
     * @return PusherMessage The created PusherMessage instance.
     */
    public function toPushNotification($notifiable)
    {
        return PusherMessage::create()
            ->web()
            ->sound('success')
            ->link(env('PUBLIC_URL'))
            ->body("A error occurred while creating the monthly report.");
    }

    public function routeNotificationFor($notification): string
    {
        return 'dashboard';
    }
}
