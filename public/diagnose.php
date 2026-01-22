<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "=== DIAGNÓSTICO DE CONEXIÓN ===\n\n";
echo "DB_HOST: " . $_ENV['DB_HOST'] . "\n";
echo "DB_NAME: " . $_ENV['DB_NAME'] . "\n";
echo "DB_USER: " . $_ENV['DB_USER'] . "\n";
echo "DB_PASS: " . (empty($_ENV['DB_PASS']) ? 'VACÍO' : 'CONFIGURADO') . "\n\n";

try {
    $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✓ Conexión exitosa\n";
    
    $stmt = $pdo->query("SELECT DATABASE()");
    echo "✓ Base de datos activa: " . $stmt->fetchColumn() . "\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
