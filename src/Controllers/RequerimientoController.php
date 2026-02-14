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
use App\Middleware\RoleMiddleware;

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

        if (!RoleMiddleware::can('requerimientos.view')) {
            $this->auditService->log('ACCESS_DENIED', 'empresa_requerimientos', null, null, ['accion' => 'index']);
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

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

        if (!RoleMiddleware::can('requerimientos.view')) {
            $this->auditService->log('ACCESS_DENIED', 'empresa_requerimientos', (int)$id, null, ['accion' => 'show']);
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $requerimiento = $this->requerimientoRepo->findWithDetails((int)$id);

        if (!$requerimiento) {
            $this->auditService->log('NOT_FOUND', 'empresa_requerimientos', (int)$id, null, ['accion' => 'show']);
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

        if (!RoleMiddleware::can('requerimientos.edit')) {
            $this->auditService->log('ACCESS_DENIED', 'empresa_requerimientos', (int)$id, null, ['accion' => 'update']);
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'observaciones' => 'max:1000'
        ];

        if (!$validator->validate($rules)) {
            $this->auditService->log('VALIDATION_ERROR', 'empresa_requerimientos', (int)$id, null, [
                'errors' => $validator->errors()
            ]);
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $requerimientoAnterior = $this->requerimientoRepo->findWithDetails((int)$id);

        if (!$requerimientoAnterior) {
            $this->auditService->log('NOT_FOUND', 'empresa_requerimientos', (int)$id, null, ['accion' => 'update']);
            $this->json(['success' => false, 'error' => 'Requerimiento no encontrado'], 404);
            return;
        }

        $requerimientoModel = new Requerimiento();
        
        $data = [
            'observaciones' => $request->post('observaciones')
        ];

        $result = $requerimientoModel->update((int)$id, $data);

        if ($result) {
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

            $this->json(['success' => true, 'message' => 'Observaciones actualizadas']);
        } else {
            $this->auditService->log('UPDATE_ERROR', 'empresa_requerimientos', (int)$id, null, ['error' => 'Error al actualizar']);
            $this->json(['success' => false, 'error' => 'Error al actualizar'], 500);
        }
    }

    public function cambiarEstado(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('requerimientos.edit')) {
            $this->auditService->log('ACCESS_DENIED', 'empresa_requerimientos', (int)$id, null, ['accion' => 'cambiarEstado']);
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $estado = $request->post('estado');

        if (!in_array($estado, ['pendiente', 'en_proceso', 'completado'])) {
            $this->auditService->log('INVALID_STATE', 'empresa_requerimientos', (int)$id, null, ['estado' => $estado]);
            $this->json(['success' => false, 'error' => 'Estado inválido'], 400);
            return;
        }

        $requerimientoAnterior = $this->requerimientoRepo->findWithDetails((int)$id);

        if (!$requerimientoAnterior) {
            $this->auditService->log('NOT_FOUND', 'empresa_requerimientos', (int)$id, null, ['accion' => 'cambiarEstado']);
            $this->json(['success' => false, 'error' => 'Requerimiento no encontrado'], 404);
            return;
        }

        $requerimientoModel = new Requerimiento();
        $result = $requerimientoModel->updateEstado((int)$id, $estado);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'empresa_requerimientos',
                (int)$id,
                [
                    'estado' => $requerimientoAnterior['estado']
                ],
                [
                    'estado' => $estado
                ]
            );

            $this->json(['success' => true, 'message' => 'Estado actualizado exitosamente']);
        } else {
            $this->auditService->log('UPDATE_ERROR', 'empresa_requerimientos', (int)$id, null, ['error' => 'Error al cambiar estado']);
            $this->json(['success' => false, 'error' => 'Error al cambiar estado'], 500);
        }
    }
}
