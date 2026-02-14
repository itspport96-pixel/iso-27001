<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class TenantMiddleware
{
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
    }

    public function handle(Request $request, Response $response): void
    {
        if (!$this->session->has('empresa_id')) {
            $response->error('Tenant no identificado', 403);
            exit;
        }

        $empresaId = $this->session->get('empresa_id');
        
        if (empty($empresaId) || !is_numeric($empresaId)) {
            $response->error('Tenant inválido', 403);
            exit;
        }

        define('TENANT_ID', (int)$empresaId);
    }

    public static function getTenantId(): ?int
    {
        $session = new Session();
        return $session->get('empresa_id');
    }

    public static function setTenantId(int $empresaId): void
    {
        $session = new Session();
        $session->set('empresa_id', $empresaId);
    }

    public static function clearTenant(): void
    {
        $session = new Session();
        $session->remove('empresa_id');
    }
}
