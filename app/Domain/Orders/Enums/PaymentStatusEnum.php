<?php

namespace App\Domain\Orders\Enums;

enum PaymentStatusEnum: int
{
    case NOT_PAID = 1;
    case PAID = 100;
    case RETURNED = 101;
    case CANCELED = 201;
}
