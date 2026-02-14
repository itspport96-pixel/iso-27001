<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function handle(Request $request, Response $response): void
    {
        if ($request->isPost()) {
            $token = $request->post('csrf_token');
            $sessionToken = $this->session->get('csrf_token');

            if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
                $response->error('CSRF token inválido', 403);
                exit;
            }
        }

        if (!$this->session->has('csrf_token')) {
            $this->generateToken();
        }
    }

    private function generateToken(): void
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
    }

    public static function getToken(): string
    {
        $session = new Session();
        
        if (!$session->has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            $session->set('csrf_token', $token);
        }

        return $session->get('csrf_token');
    }
}
