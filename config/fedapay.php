<?php

return [
    'secret_key' => env('FEDAPAY_SECRET_KEY'),
    'public_key' => env('FEDAPAY_PUBLIC_KEY'),
    'webhook_key' => env('FEDAPAY_WEBHOOK_KEY'),
    'environment' => env('FEDAPAY_ENVIRONMENT', 'sandbox'),
];
