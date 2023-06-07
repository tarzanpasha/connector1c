<?php

namespace App\Domain\Events\Actions\Orders;

use App\Domain\Events\Support\Orders\OrderEventDto;
use App\Domain\External\ExportTo1c\Api\ExportTo1cApi;
use App\Domain\Orders\Enums\PaymentStatusEnum;
use RdKafka\Message;
use Throwable;

class OrderEventAction
{
    public const CREATE = 'create';
    public const UPDATE = 'update';
    public const DELETE = 'delete';

    public function __construct(
        private readonly ExportTo1cApi $exportTo1cApi,
    ) {
    }

    /** @throws Throwable */
    public function execute(Message $kafkaMessage)
    {
        $orderEventDto = OrderEventDto::makeFromMessage($kafkaMessage);
        if ($orderEventDto->event === self::CREATE) {
            $this->exportTo1cApi->sendCustomerInfoTo1c($orderEventDto->attributes->customer_id);
        }

        if ($orderEventDto->event === self::UPDATE) {
            if ($orderEventDto->attributes->payment_status == PaymentStatusEnum::PAID) {
                $this->exportTo1cApi->sendOrderShipmentTo1c($orderEventDto->attributes->id);
            }
        }

    }
}
