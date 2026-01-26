<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Repositories\RequerimientoRepository;
use App\Services\AuditService;
use App\Models\Requerimiento;

class RequerimientoController extends Controller
{
    private RequerimientoRepository $requerimientoRepo;
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->requerimientoRepo = new RequerimientoRepository();
        $this->auditService = new AuditService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $this->requerimientoRepo->actualizarTodosLosEstados();

        $requerimientos = $this->requerimientoRepo->getWithRequerimientoBase();
        
        foreach ($requerimientos as &$req) {
            $progreso = $this->requerimientoRepo->calcularProgresoRequerimiento($req['requerimiento_id']);
            $req['progreso'] = $progreso;
        }
        
        $estadisticas = $this->requerimientoRepo->getEstadisticas();

        $this->view('requerimientos/index', [
            'requerimientos' => $requerimientos,
            'estadisticas' => $estadisticas
        ]);
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $this->requerimientoRepo->actualizarEstadoAutomatico((int)$id);

        $requerimiento = $this->requerimientoRepo->findWithDetails((int)$id);

        if (!$requerimiento) {
            $this->response->error('Requerimiento no encontrado', 404);
            return;
        }

        $controles = $this->requerimientoRepo->getControlesAsociados($requerimiento['requerimiento_id']);
        $progreso = $this->requerimientoRepo->calcularProgresoRequerimiento($requerimiento['requerimiento_id']);

        $this->view('requerimientos/show', [
            'requerimiento' => $requerimiento,
            'controles' => $controles,
            'progreso' => $progreso
        ]);
    }

    public function update(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());
        
        $rules = [
            'observaciones' => 'required|min:10'
        ];
        
        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $requerimientoAnterior = $this->requerimientoRepo->findWithDetails((int)$id);

        $requerimientoModel = new Requerimiento();
        $result = $requerimientoModel->update((int)$id, [
            'observaciones' => $request->post('observaciones')
        ]);

        if ($result) {
            $this->requerimientoRepo->actualizarEstadoAutomatico((int)$id);

            $this->auditService->log(
                'UPDATE',
                'empresa_requerimientos',
                (int)$id,
                [
                    'observaciones' => $requerimientoAnterior['observaciones']
                ],
                [
                    'observaciones' => $request->post('observaciones')
                ]
            );

            $this->json(['success' => true, 'message' => 'Observaciones actualizadas. El estado se calcula automáticamente.']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar'], 500);
        }
    }
}
