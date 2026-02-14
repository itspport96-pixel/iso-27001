<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Repositories\AuditLogRepository;
use App\Middleware\RoleMiddleware;

class AuditController extends Controller
{
    private AuditLogRepository $auditRepo;

    public function __construct()
    {
        parent::__construct();
        $this->auditRepo = new AuditLogRepository();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('audit.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $filtros = [
            'usuario_id' => $request->get('usuario_id'),
            'accion' => $request->get('accion'),
            'tabla' => $request->get('tabla'),
            'fecha_desde' => $request->get('fecha_desde'),
            'fecha_hasta' => $request->get('fecha_hasta')
        ];

        $page = max(1, (int)$request->get('page', 1));
        $perPage = 15;

        $logs = $this->auditRepo->getWithUsuario($filtros, $page, $perPage);
        $total = $this->auditRepo->countWithFiltros($filtros);
        $totalPages = ceil($total / $perPage);
        $estadisticas = $this->auditRepo->getEstadisticas();

        $this->view('audit/index', [
            'logs' => $logs,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'perPage' => $perPage
        ]);
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('audit.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $log = $this->auditRepo->find((int)$id);

        if (!$log) {
            $this->response->error('Log no encontrado', 404);
            return;
        }

        $this->view('audit/show', ['log' => $log]);
    }
}
