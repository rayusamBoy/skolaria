<?php

namespace App\Notifications;

use App\Helpers\Qs;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class TenantEventCompleted extends Notification
{
    public function __construct(protected array $data) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->notification_preferences) {
            if (\is_array($notifiable->notification_preferences) && $notifiable->notification_preferences['status']) {
                $channels = [FcmChannel::class];
            }
        }

        return $channels;
    }

    public function toFcm(): FcmMessage
    {
        $title = $this->data['title'] ?? 'Tenant update';
        $body = mb_strimwidth($this->data['msg'] ?? 'Tenant operation completed.', 0, 150, '...');

        $data = [
            'title' => $title,
            'body' => $body,
            'icon' => Qs::getAppIcon(),
            'type' => 'tenancy',
            'url' => $this->data['url'] ?? route('tenants.index'),
        ];

        return (new FcmMessage)
            ->data($data)
            ->custom([
                'android' => [
                    'notification' => [
                        'color' => '#0A0A0A',
                        'sound' => 'default',
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'analytics',
                    ],
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                        ],
                    ],
                    'fcm_options' => [
                        'analytics_label' => 'analytics',
                    ],
                ],
            ]);
    }
}
