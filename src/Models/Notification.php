<?php

namespace Ebanx\Benjamin\Models;

class Notification extends BaseModel
{
    const OPERATION_STATUS_CHANGE = 'payment_status_change';

    const NOTIFICATION_TYPE_UPDATE = 'update';
    const NOTIFICATION_TYPE_REFUND = 'refund';
    const NOTIFICATION_TYPE_CHARGEBACK = 'chargeback';

    public $operation;
    public $type;
    public $hashCodes = [];
}
