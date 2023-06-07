<?php

namespace App\Domain\Orders\Data\Orders;

use Layta\OmsClient\Dto\Order;

class OrderData
{
    public function __construct(public readonly Order $order)
    {
    }
}
