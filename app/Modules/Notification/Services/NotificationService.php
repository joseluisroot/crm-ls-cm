<?php

namespace Modules\Notification\Services;

use Modules\Notification\Models\NotificationModel;
use Modules\Notification\Support\NotificationChannels;
use Modules\Notification\Support\NotificationStatus;

class NotificationService
{
    public function createInternal(
        string $subject,
        string $body,
        ?string $recipientId = null,
        array $payload = []
    ): int {
        return (int) (new NotificationModel())->insert([
            'channel' => NotificationChannels::INTERNAL,
            'recipient_type' => 'admin',
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
            'status' => NotificationStatus::PENDING,
            'payload' => json_encode($payload),
        ]);
    }

    public function markAsSent(int $notificationId): bool
    {
        return (bool) (new NotificationModel())->update($notificationId, [
            'status' => NotificationStatus::SENT,
            'sent_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markAsRead(int $notificationId): bool
    {
        return (bool) (new NotificationModel())->update($notificationId, [
            'status' => NotificationStatus::READ,
            'read_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function fail(int $notificationId, ?string $reason = null): bool
    {
        return (bool) (new NotificationModel())->update($notificationId, [
            'status' => NotificationStatus::FAILED,
            'payload' => json_encode([
                'error' => $reason,
            ]),
        ]);
    }
}