<?php

if (!function_exists('topic')) {
    function topic(string $name): string
    {
        return config('kafka.contour') . ".$name";
    }
}

if (!function_exists('get_raw_number')) {
    /**
     * @param $phone
     * @return string
     */
    function get_raw_number($phone)
    {
        $matches = [];
        preg_match('/([\s\d()-]+)/', $phone, $matches);

        return preg_replace("/[^0-9]/", '', $matches[1]);
    }
}

if (!function_exists('absolute_url')) {
    /**
     * @param string $page
     * @return string
     */
    function absolute_url($page = '')
    {
        $domain = config('app.url') . '/';

        return $domain . ltrim($page, '/');
    }
}

if (!function_exists('phone_print')) {

    /**
     * получить телефоне в формате для печати
     * @param $number
     * @return int
     */
    function phone_print($number)
    {
        $ph = (string)phone_number(phone_format($number));
        if (!$ph) {
            return "";
        }

        $ph = '+' . $ph[0] . '(' . $ph[1] . $ph[2] . $ph[3] . ')' . $ph[4] . $ph[5] . $ph[6] . '-' . $ph[7] . $ph[8] . '-' . $ph[9] . $ph[10];

        return $ph;
    }
}

if (!function_exists('phone_format')) {
    /**
     * Привести телефон к формату хранения в бд
     *
     * @param string $phone
     * @return string
     */
    function phone_format($phone)
    {
        $phone = phone_number($phone);

        if (strlen($phone) === 10) {
            $phone = "+7{$phone}";
        }

        if (strlen($phone) === 11) {
            $phone = "+{$phone}";
        }

        return $phone;
    }
}

if (!function_exists('phone_number')) {

    /**
     * получить только числа из номера
     * @param $number
     * @return int
     */
    function phone_number($number)
    {
        $matches = [];
        preg_match('/([\s\d()-]+)/', $number, $matches);

        if (!isset($matches[1])) {
            return '';
        }

        return preg_replace("/[^0-9]/", '', $matches[1]);
    }
}

if (!function_exists('month_name')) {
    /**
     * Получение названия месяца в нужном падеже.
     *
     * @param int $monthIndex
     * @param bool $genitive
     * @return string
     */
    function month_name($monthIndex, $genitive = false)
    {
        $monthIndex = (int)$monthIndex - 1;

        $monthNames = [
            ["январь", "января"],
            ["февраль", "февраля"],
            ["март", "марта"],
            ["апрель", "апреля"],
            ["май", "мая"],
            ["июнь", "июня"],
            ["июль", "июля"],
            ["август", "августа"],
            ["сентябрь", "сентября"],
            ["октябрь", "октября"],
            ["ноябрь", "ноября"],
            ["декабрь", "декабря"],
        ];

        return isset($monthNames[$monthIndex])
            ? ($genitive ? $monthNames[$monthIndex][1] : $monthNames[$monthIndex][0])
            : '';
    }
}

if (!function_exists('weekday_name')) {
    /**
     * Получение названия дня недели.
     *
     * @param int $weekdayIndex
     * @return string
     */
    function weekday_name($weekdayIndex)
    {
        $weekdayIndex = (int)$weekdayIndex;

        $weekdaysNames = [
            "воскресенье",
            "понедельник",
            "вторник",
            "среда",
            "четверг",
            "пятница",
            "суббота",
        ];

        return $weekdaysNames[$weekdayIndex] ?? '';
    }
}

if (!function_exists('cop2rub')) {

    /**
     * Перевести копейки в рубли
     * @param int $value - значение в копейках
     * @return float
     */
    function cop2rub(int $value): float
    {
        return round($value / 100, 2);
    }
}

if (!function_exists('price_format')) {

    /**
     * Вывести число в виде цены
     * @param float $value
     * @return string
     */
    function price_format(float $value): string
    {
        return number_format($value, 2, ',', ' ');
    }
}

if (!function_exists('in_production')) {

    /**
     * Находится ли приложение в прод режиме
     * @return bool
     */
    function in_production()
    {
        return config('app.env', 'production') == 'production';
    }
}

if (!function_exists('snake_to_camel_case')) {

    /**
     * Переводит строку из snake в camel case
     * @param string $snake
     * @param bool $firstToUpper
     * @return string
     */
    function snake_to_camel_case(string $snake, bool $firstToUpper = false)
    {
        $camel = str_replace(' ', '', ucwords(str_replace('_', ' ', $snake)));

        if (!$firstToUpper) {
            $camel = lcfirst($camel);
        }

        return $camel;
    }
}
