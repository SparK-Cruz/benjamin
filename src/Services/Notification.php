<?php

namespace Ebanx\Benjamin\Services;

use Ebanx\Benjamin\Models\Notification as Model;

class Notification
{
    public function isValid(Model $notification)
    {
        return self::isOperationStatusChange($notification)
            && self::isNotificationTypeSupported($notification)
            && self::hasHashCodes($notification);
    }

    private static function isOperationStatusChange(Model $notification)
    {
        return $notification->operation === Model::OPERATION_STATUS_CHANGE;
    }

    private static function isNotificationTypeSupported(Model $notification)
    {
        return in_array(
            $notification->type,
            [
                Model::NOTIFICATION_TYPE_UPDATE,
                Model::NOTIFICATION_TYPE_REFUND,
                Model::NOTIFICATION_TYPE_CHARGEBACK,
            ]
        );
    }

    private static function hasHashCodes(Model $notification)
    {
        return count($notification->hashCodes) > 0;
    }
}
