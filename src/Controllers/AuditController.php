<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Repositories\AuditLogRepository;

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

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $filtros = [
            'usuario_id' => $request->get('usuario_id'),
            'accion' => $request->get('accion'),
            'tabla' => $request->get('tabla'),
            'fecha_desde' => $request->get('fecha_desde'),
            'fecha_hasta' => $request->get('fecha_hasta')
        ];

        $logs = $this->auditRepo->getWithUsuario($filtros);
        $estadisticas = $this->auditRepo->getEstadisticas();

        $this->view('audit/index', [
            'logs' => $logs,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros
        ]);
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

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
