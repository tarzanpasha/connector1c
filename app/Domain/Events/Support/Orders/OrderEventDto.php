<?php

namespace App\Domain\Events\Support\Orders;

use Illuminate\Support\Fluent;
use RdKafka\Message;

/**
 * @property array[] $dirty - массив изменившихся атрибутов
 * @property OrderDto $attributes - данные по заказу
 * @property string $event - тип события
 */
class OrderEventDto extends Fluent
{
    /**
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $attributes['attributes'] = new OrderDto($attributes['attributes'] ?? []);
        parent::__construct($attributes);
    }

    public static function makeFromMessage(Message $message): self
    {
        return new self((array)json_decode($message->payload, true));
    }
}
