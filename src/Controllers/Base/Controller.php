<?php

namespace App\Controllers\Base;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;

abstract class Controller
{
    protected Request $request;
    protected Response $response;
    protected Session $session;
    protected AuthService $auth;

    public function __construct()
    {
        $this->session = new Session();
        $this->auth = new AuthService();
    }

    protected function view(string $view, array $data = []): void
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../../Views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            $this->response->error("Vista no encontrada: {$view}", 404);
            return;
        }
        
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        $this->response->html($content);
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        $this->response->json($data, $statusCode);
    }

    protected function redirect(string $url): void
    {
        $this->response->redirect($url);
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->response->redirect($referer);
    }

    protected function user(): ?array
    {
        return $this->auth->user();
    }

    protected function isAuthenticated(): bool
    {
        return $this->auth->check();
    }

    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            exit;
        }
    }

    protected function requireRole(string $role): void
    {
        $this->requireAuth();
        
        $user = $this->user();
        
        if ($user['rol'] !== $role && $user['rol'] !== 'super_admin') {
            $this->response->error('Acceso denegado', 403);
            exit;
        }
    }
}
