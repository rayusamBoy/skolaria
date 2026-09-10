<?php

namespace App\Notifications;

use App\Helpers\Qs;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmMessage;

class NotificationMarkedAsRead extends Notification
{
    use Queueable;

    public function __construct(protected string $notification_id) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = Qs::getActiveNotificationChannels($notifiable, false, false, true);

        return $channels;
    }

    public function toFcm(): FcmMessage
    {
        $data = [
            'id' => $this->notification_id,
            'type' => 'notification_marked_as_read',
        ];

        return (new FcmMessage)->data($data);
    }
}
