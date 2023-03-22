<?php

use Illuminate\Support\Str;

return [
    'telegram' => [
        'token' => env('TELEGRAM_TOKEN'),
        'manager'=>env('TELEGRAM_MANAGER'),
        'admin'=>env('TELEGRAM_ADMIN'),
    ],
];
