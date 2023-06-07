<?php

return [
    'catalog' => [
        'catalog-cache' => [
            'base_uri' => env('CATALOG_CATALOG_CACHE_SERVICE_HOST') . "/api/v1",
        ],
        'offers' => [
            'base_uri' => env('CATALOG_OFFERS_SERVICE_HOST') . "/api/v1",
        ],
        'pim' => [
            'base_uri' => env('CATALOG_PIM_SERVICE_HOST') . "/api/v1",
        ],
    ],
    'customers' => [
        'customer-auth' => [
            'base_uri' => env('CUSTOMERS_CUSTOMER_AUTH_SERVICE_HOST') . "/api/v1",
            'client' => [
                'id' => env('CUSTOMERS_CUSTOMER_AUTH_SERVICE_CLIENT_ID', ''),
                'secret' => env('CUSTOMERS_CUSTOMER_AUTH_SERVICE_CLIENT_SECRET', ''),
            ],
        ],
        'customers' => [
            'base_uri' => env('CUSTOMERS_CUSTOMERS_SERVICE_HOST') . "/api/v1",
        ],
    ],
    'orders' => [
        'baskets' => [
            'base_uri' => env('ORDERS_BASKETS_SERVICE_HOST') . "/api/v1",
        ],
        'oms' => [
            'base_uri' => env('ORDERS_OMS_SERVICE_HOST') . "/api/v1",
        ],
    ],
    'logistic' => [
        'logistic' => [
            'base_uri' => env('LOGISTIC_LOGISTIC_SERVICE_HOST') . "/api/v1",
        ],
    ],
    'units' => [
        'bu' => [
            'base_uri' => env('UNITS_BU_SERVICE_HOST') . "/api/v1",
        ],
    ],
    'marketing' => [
        'marketing' => [
            'base_uri' => env('MARKETING_MARKETING_SERVICE_HOST') . "/api/v1",
        ],
    ],
    'cms' => [
        'cms' => [
            'base_uri' => env('CMS_CMS_SERVICE_HOST') . "/api/v1",
        ],
    ],
];
