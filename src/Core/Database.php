<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?self $instance = null;
    private PDO $connection;

    private function __construct()
    {
        $cfg = require __DIR__ . '/../../config/database.php';
        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['driver'],
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset']
        );
        try {
            $this->connection = new PDO(
                $dsn,
                $cfg['username'],
                $cfg['password'],
                $cfg['options']
            );
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Database connection error');
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function __clone() {}
    public function __wakeup() {}
}
