<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use PDO;

class RateLimitMiddleware
{
    private PDO $db;
    private int $maxAttempts;
    private int $decaySeconds;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->maxAttempts = 1000;
        $this->decaySeconds = 30; // 30 segundos en lugar de 0.5 minutos
    }

    public function handle(Request $request, Response $response): void
    {
        $key = $this->resolveRequestKey($request);

        $this->cleanExpiredRecords();

        $record = $this->getRecord($key);

        $now = time();

        if ($record) {
            $expiresAt = strtotime($record['expires_at']);

            if ($expiresAt < $now) {
                $this->resetRecord($key);
                $record = null;
            }
        }

        $attempts = $record ? (int)$record['attempts'] : 0;

        if ($attempts >= $this->maxAttempts) {
            $expiresAt = strtotime($record['expires_at']);
            $retryAfter = $expiresAt - $now;
            $response->setHeader('Retry-After', (string)max(1, $retryAfter));
            $response->error('Demasiados intentos. Intenta nuevamente en ' . ceil($retryAfter / 60) . ' minutos', 429);
            exit;
        }

        $this->incrementAttempts($key);
    }

    private function resolveRequestKey(Request $request): string
    {
        $uri = $request->uri();

        if ($uri === '/login' || $uri === '/register') {
            return hash('sha256', $request->ip() . '|' . $uri);
        }

        return hash('sha256', $request->ip() . '|' . $uri);
    }

    private function getRecord(string $key): ?array
    {
        $sql = "SELECT * FROM rate_limits WHERE rate_key = :key LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':key', $key);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function incrementAttempts(string $key): void
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->decayMinutes} minutes"));

        $sql = "INSERT INTO rate_limits (rate_key, attempts, last_attempt, expires_at)
                VALUES (:key, 1, NOW(), :expires_at)
                ON DUPLICATE KEY UPDATE
                    attempts = attempts + 1,
                    last_attempt = NOW()";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':key', $key);
        $stmt->bindValue(':expires_at', $expiresAt);
        $stmt->execute();
    }

    private function resetRecord(string $key): void
    {
        $sql = "DELETE FROM rate_limits WHERE rate_key = :key";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':key', $key);
        $stmt->execute();
    }

    private function cleanExpiredRecords(): void
    {
        if (rand(1, 100) > 1) {
            return;
        }

        $sql = "DELETE FROM rate_limits WHERE expires_at < NOW()";
        $this->db->exec($sql);
    }

    public function clear(Request $request): void
    {
        $key = $this->resolveRequestKey($request);
        $this->resetRecord($key);
    }
}
