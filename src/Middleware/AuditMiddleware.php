<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuditService;

class AuditMiddleware
{
    private AuditService $auditService;

    public function __construct()
    {
        $this->auditService = new AuditService();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $method = $request->method();
        $uri = $request->uri();

        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $tabla = $this->inferirTabla($uri);
            $accion = $this->inferirAccion($method, $uri);

            if ($tabla && $accion) {
                $this->auditService->log($accion, $tabla);
            }
        }

        $next($request, $response);
    }

    private function inferirTabla(string $uri): ?string
    {
        if (strpos($uri, '/controles') !== false) return 'soa_entries';
        if (strpos($uri, '/gaps') !== false) return 'gap_items';
        if (strpos($uri, '/evidencias') !== false) return 'evidencias';
        if (strpos($uri, '/requerimientos') !== false) return 'empresa_requerimientos';

        return null;
    }

    private function inferirAccion(string $method, string $uri): ?string
    {
        if ($method === 'POST' && strpos($uri, '/update') !== false) return 'actualizar';
        if ($method === 'POST' && strpos($uri, '/delete') !== false) return 'eliminar';
        if ($method === 'POST' && strpos($uri, '/store') !== false) return 'crear';
        if ($method === 'POST' && strpos($uri, '/validar') !== false) return 'validar';

        return null;
    }
}
