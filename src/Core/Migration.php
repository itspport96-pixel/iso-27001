<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class Migration
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function run(string $sqlFile): void
    {
        if (!file_exists($sqlFile)) {
            throw new \RuntimeException("Migration file not found: $sqlFile");
        }
        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new \RuntimeException("Cannot read migration file: $sqlFile");
        }
        try {
            $this->db->exec($sql);
        } catch (\PDOException $e) {
            error_log("Migration failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function seed(string $sqlFile): void
    {
        $this->run($sqlFile);
    }
}
