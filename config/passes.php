<?php

return [
    'apple' => [
        'certificate' => storage_path('app/' . env('APPLE_WALLET_CERTIFICATE_PATH', 'wallet/pass.p12')),
        'wwdr' => storage_path('app/' . env('APPLE_WALLET_WWDR_PATH', 'wallet/wwdr.pem')),
        'password' => env('APPLE_WALLET_CERTIFICATE_PASSWORD'),
        'disk' => env('MEDIA_DISK', 'public'),
        'temp_dir' => sys_get_temp_dir(),
    ],

    'google' => [
        'credentials' => storage_path('app/' . env('GOOGLE_WALLET_SERVICE_ACCOUNT_PATH', 'wallet/google-service-account.json')),
        'origins' => [env('APP_URL')],
    ],
];
