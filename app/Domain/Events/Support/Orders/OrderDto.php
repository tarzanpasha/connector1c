<?php

namespace App\Domain\Events\Support\Orders;

use Illuminate\Support\Carbon;
use Illuminate\Support\Fluent;

/**
 * @property int $id id заказа
 * @property string $number номер заказа
 *
 * @property int $basket_id id корзины
 * @property int $seller_id id продавца
 *
 * @property int $customer_id id покупателя
 * @property string $customer_email почта покупателя
 *
 * @property float $cost стоимость до скидок (расчитывается автоматически)
 * @property float $price стоимость со скидками (расчитывается автоматически)
 *
 * @property int $delivery_service служба доставки
 * @property int $delivery_method метод доставки
 * @property float $delivery_cost стоимость доставки (без учета скидки)
 * @property float $delivery_price стоимость доставки (с учетом скидки)
 * @property int $delivery_tariff_id - идентификатор тарифа на доставку из сервиса логистики
 * @property int $delivery_point_id - идентификатор пункта самовывоза из сервиса логистики
 * @property array $delivery_address - адрес доставки
 * @property string|null $delivery_comment комментарий к доставке
 *
 * @property string $receiver_name - имя получателя
 * @property string $receiver_phone - телефон получателя
 * @property string $receiver_email - e-mail получателя
 *
 * @property int $spent_bonus списано бонусов
 * @property int $added_bonus начислено бонусов
 * @property array|null $certificates использованные сертификаты
 *
 * @property int $status статус
 * @property Carbon|null $status_at дата установки статуса заказа
 *
 * @property int $payment_status статус оплаты
 * @property Carbon|null $payment_status_at дата установки статуса оплаты
 * @property Carbon|null $payed_at дата оплаты
 * @property int $payment_system система оплаты
 * @property int $payment_method метод оплаты
 * @property Carbon|null $payment_expires_at дата, когда оплата станет просрочена
 * @property string|null $payment_link ссылка на оплату во внешней системе
 * @property string|null $payment_external_id id оплаты во внешней системе
 *
 * @property int $is_expired флаг, что заказ просроченный
 * @property Carbon|null $is_expired_at дата установки флага просроченного заказа
 * @property int $is_return флаг, что заказ возвращен
 * @property Carbon|null $is_return_at дата установки флага возвращенного заказа
 * @property int $is_partial_return флаг, что заказ частично возвращен
 * @property Carbon|null $is_partial_return_at дата установки флага частично возвращенного заказа
 * @property int $is_problem флаг, что заказ проблемный
 * @property Carbon|null $is_problem_at дата установки флага проблемного заказа
 * @property string $assembly_problem_comment последнее сообщение продавца о проблеме со сборкой
 *
 * @property string|null $manager_comment комментарий менеджера
 *
 * @property Carbon|null $created_at дата создание
 * @property Carbon|null $updated_at дата обновления
 *
 */
class OrderDto extends Fluent
{
}
