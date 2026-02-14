<?php

return [
    'lifetime' => $_ENV['SESSION_LIFETIME'] ?? 7200,
    'name' => $_ENV['SESSION_NAME'] ?? 'iso_session',
    'path' => $_ENV['SESSION_PATH'] ?? '/var/www/html/storage/sessions',
    'cookie' => [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $_ENV['APP_ENV'] === 'production',
        'httponly' => true,
        'samesite' => 'Strict'
    ],
    'gc_probability' => 1,
    'gc_divisor' => 100,
    'gc_maxlifetime' => 7200
];
