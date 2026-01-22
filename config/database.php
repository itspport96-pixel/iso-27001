<?php
declare(strict_types=1);

return [
    'driver'    => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host'      => $_ENV['DB_HOST']      ?? '192.168.10.4',
    'port'      => (int)($_ENV['DB_PORT'] ?? 3306),
    'database'  => $_ENV['DB_DATABASE']  ?? 'iso_platform',
    'username'  => $_ENV['DB_USERNAME']  ?? 'testdb_user',
    'password'  => $_ENV['DB_PASSWORD']  ?? 'Temporal2024#',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => true,
    ],
];
