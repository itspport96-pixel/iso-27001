<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\{Session, Database};

final class RateLimitService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $decayMinutes): bool
    {
        $cache = $this->getAttempts($key);
        if ($cache >= $maxAttempts) {
            return true;
        }
        $this->increment($key, $decayMinutes * 60);
        return false;
    }

    public function getAttempts(string $key): int
    {
        $stmt = $this->db->getConnection()->prepare(
            'SELECT attempts FROM rate_limits WHERE cache_key = :key AND expires_at > NOW() LIMIT 1'
        );
        $stmt->execute(['key' => $key]);
        return (int)$stmt->fetchColumn();
    }

    public function increment(string $key, int $ttlSeconds): void
    {
        $conn = $this->db->getConnection();
        $conn->prepare(
            'INSERT INTO rate_limits (cache_key, attempts, expires_at)
             VALUES (:key, 1, DATE_ADD(NOW(), INTERVAL :ttl SECOND))
             ON DUPLICATE KEY UPDATE attempts = attempts + 1'
        )->execute(['key' => $key, 'ttl' => $ttlSeconds]);
    }

    public function clear(string $key): void
    {
        $this->db->getConnection()->prepare('DELETE FROM rate_limits WHERE cache_key = :key')->execute(['key' => $key]);
    }
}
