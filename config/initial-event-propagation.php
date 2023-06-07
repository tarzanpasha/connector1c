<?php

return [
    'app_code' => 'connectors--1c_connector',
    'set_initial_event_http_middleware' => [
        /**
         * If is set to `true` the middleware does not override the InitialEvent if it was already set for current context earlier.
         * Defaults to `false`.
         */
        'preserve_existing_event' => true,
    ],
];
