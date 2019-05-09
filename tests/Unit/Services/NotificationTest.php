<?php

namespace Tests\Unit\Services;

use Ebanx\Benjamin\Models\Notification;
use Ebanx\Benjamin\Services\Notification as Service;
use Tests\TestCase;

class HttpTest extends TestCase
{
    public static function getNotificationsData()
    {
        return [
            [
                new Notification([
                    'operation' => 'payment_status_change',
                    'type' => 'update',
                    'hashCodes' => ['123'],
                ]),
                true
            ],
            [
                new Notification([
                    'operation' => 'payment_status_change',
                    'type' => 'refund',
                    'hashCodes' => ['123'],
                ]),
                true
            ],
            [
                new Notification([
                    'operation' => 'payment_status_change',
                    'type' => 'invalid',
                    'hashCodes' => ['123'],
                ]),
                false
            ],
            [
                new Notification([
                    'operation' => 'payment_status_changes',
                    'type' => 'update',
                    'hashCodes' => ['123'],
                ]),
                false
            ],
            [
                new Notification([
                    'operation' => 'payment_status_change',
                    'type' => 'update',
                    'hashCodes' => [],
                ]),
                false
            ],
        ];
    }

    /**
     * @dataProvider getNotificationsData
     */
    public function testValidNotification($notification, $valid)
    {
        $this->assertEquals($valid, (new Service())->isValid($notification));
    }

    public function getValidNotificationGets()
    {
        $expected_operation = 'payment_status_change';
        $expected_notification_type = 'update';
        $expected_hash  = ['123'];
        $notification = new Notification($expected_operation, $expected_notification_type, $expected_hash);

        $this->assertEquals($expected_operation, $notification->getOperation());
        $this->assertEquals($expected_notification_type, $notification->getNotificationType());
        $this->assertEquals($expected_hash, $notification->getHashCodes());
    }
}
