<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function handle(Request $request, Response $response): void
    {
        if (!$this->session->has('user_id')) {
            $response->redirect('/login');
            exit;
        }

        $lastActivity = $this->session->get('last_activity', 0);
        $timeout = 1800; // 30 minutos

        if (time() - $lastActivity > $timeout) {
            $this->session->destroy();
            $response->redirect('/login?timeout=1');
            exit;
        }

        $this->session->set('last_activity', time());
    }

    public static function check(): bool
    {
        $session = new Session();
        return $session->has('user_id');
    }

    public static function user(): ?array
    {
        $session = new Session();
        
        if (!$session->has('user_id')) {
            return null;
        }

        return [
            'id' => $session->get('user_id'),
            'email' => $session->get('user_email'),
            'rol' => $session->get('user_rol'),
            'empresa_id' => $session->get('empresa_id')
        ];
    }

    public static function logout(): void
    {
        $session = new Session();
        $session->destroy();
    }
}
