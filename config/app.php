<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

return [
    'name'      => $_ENV['APP_NAME']      ?? 'ISO27001 Platform',
    'env'       => $_ENV['APP_ENV']       ?? 'local',
    'debug'     => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'       => $_ENV['APP_URL']       ?? 'http://localhost',
    'timezone'  => 'America/Lima',
    'locale'    => 'es_PE',
    'encoding'  => 'UTF-8',
];
