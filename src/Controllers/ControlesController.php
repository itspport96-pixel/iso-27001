<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Repositories\ControlRepository;
use App\Repositories\SOARepository;
use App\Services\AuditService;
use App\Middleware\RoleMiddleware;

class ControlController extends Controller
{
    private ControlRepository $controlRepo;
    private SOARepository $soaRepo;
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->controlRepo = new ControlRepository();
        $this->soaRepo = new SOARepository();
        $this->auditService = new AuditService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('controles.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $dominioId = $request->get('dominio');
        $estado = $request->get('estado');
        $aplicable = $request->get('aplicable');

        if ($dominioId || $estado || $aplicable !== null) {
            $soas = $this->soaRepo->getByFiltros($dominioId, $estado, $aplicable);
        } else {
            $soas = $this->soaRepo->getWithControlInfo();
        }

        $dominios = $this->controlRepo->getAllDominios();
        $estadisticas = $this->soaRepo->getEstadisticas();

        $this->view('controles/index', [
            'soas' => $soas,
            'dominios' => $dominios,
            'estadisticas' => $estadisticas,
            'filtro_dominio' => $dominioId,
            'filtro_estado' => $estado,
            'filtro_aplicable' => $aplicable
        ]);
    }

    public function search(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        
        try {
            $this->requireAuth();

            if (!RoleMiddleware::can('controles.view')) {
                $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
                return;
            }

            $empresaId = $this->user()['empresa_id'];
            
            if (!$empresaId) {
                $this->json(['success' => false, 'error' => 'Empresa no identificada'], 400);
                return;
            }
            
            TenantContext::getInstance()->setTenant($empresaId);

            $searchQuery = $request->get('search') ?? '';
            $page = max(1, (int)($request->get('page') ?? 1));
            $perPage = max(1, min(100, (int)($request->get('per_page') ?? 10)));
            
            $dominioId = $request->get('dominio');
            $estado = $request->get('estado');
            $aplicable = $request->get('aplicable');

            $result = $this->soaRepo->searchWithPagination(
                $searchQuery,
                $page,
                $perPage,
                $dominioId,
                $estado,
                $aplicable
            );

            $this->json([
                'success' => true,
                'data' => $result['data'],
                'pagination' => [
                    'page' => $result['page'],
                    'per_page' => $result['per_page'],
                    'total' => $result['total'],
                    'last_page' => $result['last_page']
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log("ControlController::search ERROR: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            $this->json([
                'success' => false, 
                'error' => 'Error interno del servidor',
                'debug' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : null
            ], 500);
        }
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('controles.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $soa = $this->soaRepo->findById((int)$id);

        if (!$soa) {
            $this->response->error('Control no encontrado', 404);
            return;
        }

        $soaDetalle = $this->soaRepo->findByControlId($soa['control_id']);

        $this->view('controles/show', [
            'soa' => $soaDetalle
        ]);
    }

    public function update(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('controles.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $estado = $request->post('estado');
        $justificacion = $request->post('justificacion');
        $aplicable = $request->post('aplicable');

        if (!in_array($estado, ['no_implementado', 'parcial', 'implementado'])) {
            $this->json(['success' => false, 'error' => 'Estado invalido'], 400);
            return;
        }

        $soaAnterior = $this->soaRepo->findById((int)$id);

        if (!$soaAnterior) {
            $this->json(['success' => false, 'error' => 'Control no encontrado'], 404);
            return;
        }

        $data = [
            'estado' => $estado,
            'justificacion' => $justificacion,
            'aplicable' => $aplicable ? 1 : 0,
            'fecha_evaluacion' => date('Y-m-d')
        ];

        $soaModel = new \App\Models\SOA();
        $result = $soaModel->update((int)$id, $data);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'soa_entries',
                (int)$id,
                [
                    'estado' => $soaAnterior['estado'],
                    'aplicable' => $soaAnterior['aplicable'],
                    'justificacion' => $soaAnterior['justificacion']
                ],
                [
                    'estado' => $estado,
                    'aplicable' => $aplicable ? 1 : 0,
                    'justificacion' => $justificacion
                ]
            );

            $this->json(['success' => true, 'message' => 'Control actualizado']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar'], 500);
        }
    }

    public function estadisticas(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('controles.view')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $estadisticas = $this->soaRepo->getEstadisticas();
        $porDominio = $this->soaRepo->getEstadisticasPorDominio();

        $this->json([
            'success' => true,
            'generales' => $estadisticas,
            'por_dominio' => $porDominio
        ]);
    }
}
