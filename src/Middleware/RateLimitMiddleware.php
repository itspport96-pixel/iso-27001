<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class RateLimitMiddleware
{
    private Session $session;
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->session = new Session();
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function handle(Request $request, Response $response): void
    {
        $key = $this->resolveRequestKey($request);
        $attempts = $this->session->get("rate_limit_{$key}_attempts", 0);
        $lastAttempt = $this->session->get("rate_limit_{$key}_time", 0);

        $now = time();
        $decaySeconds = $this->decayMinutes * 60;

        if ($now - $lastAttempt > $decaySeconds) {
            $attempts = 0;
        }

        if ($attempts >= $this->maxAttempts) {
            $retryAfter = $decaySeconds - ($now - $lastAttempt);
            $response->setHeader('Retry-After', (string)$retryAfter);
            $response->error('Demasiados intentos. Intenta nuevamente en ' . ceil($retryAfter / 60) . ' minutos', 429);
            exit;
        }

        $attempts++;
        $this->session->set("rate_limit_{$key}_attempts", $attempts);
        $this->session->set("rate_limit_{$key}_time", $now);
    }

    private function resolveRequestKey(Request $request): string
    {
        return hash('sha256', $request->ip() . '|' . $request->uri());
    }

    public function clear(Request $request): void
    {
        $key = $this->resolveRequestKey($request);
        $this->session->remove("rate_limit_{$key}_attempts");
        $this->session->remove("rate_limit_{$key}_time");
    }
}
