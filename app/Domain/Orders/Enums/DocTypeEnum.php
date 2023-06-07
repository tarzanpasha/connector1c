<?php

namespace App\Domain\Orders\Enums;

enum DocTypeEnum: int
{
    case ARRIVAL = 1;
    case REFUND_FROM_CUSTOMER = 2;
    case SHIPMENT = 3;
    case REFUND_TO_SELLER = 4;
}
