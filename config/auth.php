<?php

return [
    'guards' => [
        'api' => [
            'driver' => 'token',
            'provider' => 'employees',
        ],
    ],

    'providers' => [
        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employee::class,
        ],
    ],
];
