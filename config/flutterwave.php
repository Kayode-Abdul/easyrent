<?php

return [
    'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),
    'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),
    'encryption_key' => env('FLUTTERWAVE_ENCRYPTION_KEY'),
    'payment_url' => env('FLUTTERWAVE_PAYMENT_URL', 'https://api.flutterwave.com/v3'),
];