<?php
return [
    
        'driver' => 'mongodb',

        'dsn' => env('MONGODB_URI', 'mongodb://localhost:27017'),

        'database' => env('MONGODB_DATABASE', 'laravel_bank'),
];