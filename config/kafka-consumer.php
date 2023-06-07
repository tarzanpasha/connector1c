<?php

use App\Domain\Events\Actions\Orders\OrderEventAction;

return [
    'processors' => [
        [
            'topic' => topic('orders.fact.orders.1'),
            'consumer' => 'default',
            'type' => 'action',
            'class' => OrderEventAction::class,
            'queue' => false,
            'consume_timeout' => 5000,
        ]
    ],
];
