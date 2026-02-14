<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Repositories\GapRepository;
use App\Services\AuditService;
use App\Models\Gap;
use App\Models\Accion;
use App\Middleware\RoleMiddleware;

class GapController extends Controller
{
    private GapRepository $gapRepo;
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->gapRepo = new GapRepository();
        $this->auditService = new AuditService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $prioridad = $request->get('prioridad');

        if ($prioridad) {
            $gaps = $this->gapRepo->getByPrioridad($prioridad);
        } else {
            $gaps = $this->gapRepo->getWithControlInfo();
        }

        $estadisticas = $this->gapRepo->getEstadisticas();

        $this->view('gap/index', [
            'gaps' => $gaps,
            'estadisticas' => $estadisticas,
            'filtro_prioridad' => $prioridad
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.create')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $controles = $this->gapRepo->getControlesAplicablesNoImplementados();

        $this->view('gap/create', [
            'controles' => $controles
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.create')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'soa_id' => 'required|numeric',
            'brecha' => 'required|min:10',
            'impacto' => 'required|in:critico,alto,medio,bajo',
            'prioridad' => 'required|in:alta,media,baja'
        ];

        if (!$validator->validate($rules)) {
            $errores = $validator->errors();
            $mensaje = 'Errores de validacion: ';
            foreach ($errores as $campo => $error) {
                $mensaje .= $error[0] . '; ';
            }
            $this->session->flash('error', rtrim($mensaje, '; '));
            $response->redirect('/gaps/create');
            return;
        }

        $soaId = (int)$request->post('soa_id');

        if (!$this->gapRepo->validarSoaPermiteGap($soaId)) {
            $this->session->flash('error', 'El control seleccionado no permite crear un GAP o ya tiene uno activo');
            $response->redirect('/gaps/create');
            return;
        }

        try {
            $this->gapRepo->beginTransaction();

            $gapModel = new Gap();
            $gapId = $gapModel->create([
                'soa_id' => $soaId,
                'brecha' => $request->post('brecha'),
                'impacto' => $request->post('impacto'),
                'prioridad' => $request->post('prioridad'),
                'fecha_objetivo' => $request->post('fecha_objetivo'),
                'avance' => 0
            ]);

            if (!$gapId) {
                throw new \Exception('Error al crear GAP');
            }

            $this->auditService->log(
                'INSERT',
                'gap_items',
                $gapId,
                null,
                [
                    'soa_id' => $soaId,
                    'brecha' => $request->post('brecha'),
                    'impacto' => $request->post('impacto'),
                    'prioridad' => $request->post('prioridad')
                ]
            );

            $descripciones = $_POST['accion_descripcion'] ?? [];
            $responsables = $_POST['accion_responsable'] ?? [];
            $fechas = $_POST['accion_fecha'] ?? [];

            $accionModel = new Accion();

            for ($i = 0; $i < count($descripciones); $i++) {
                if (!empty($descripciones[$i]) && !empty($fechas[$i])) {
                    $accionId = $accionModel->create([
                        'gap_id' => $gapId,
                        'descripcion' => $descripciones[$i],
                        'responsable' => $responsables[$i] ?? null,
                        'fecha_compromiso' => $fechas[$i],
                        'estado' => 'pendiente'
                    ]);

                    if ($accionId) {
                        $this->auditService->log(
                            'INSERT',
                            'acciones',
                            $accionId,
                            null,
                            [
                                'gap_id' => $gapId,
                                'descripcion' => $descripciones[$i],
                                'estado' => 'pendiente'
                            ]
                        );
                    }
                }
            }

            $this->gapRepo->commit();

            $this->session->flash('success', 'GAP creado exitosamente');
            $response->redirect('/gaps/' . $gapId);

        } catch (\Exception $e) {
            $this->gapRepo->rollback();
            $this->session->flash('error', 'Error al crear el GAP: ' . $e->getMessage());
            $response->redirect('/gaps/create');
        }
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $gap = $this->gapRepo->findWithDetails((int)$id);

        if (!$gap) {
            $this->response->error('GAP no encontrado', 404);
            return;
        }

        $accionModel = new Accion();
        $acciones = $accionModel->getByGapId((int)$id);

        $this->view('gap/show', [
            'gap' => $gap,
            'acciones' => $acciones
        ]);
    }

    public function update(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'brecha' => 'required|min:10',
            'impacto' => 'required|in:critico,alto,medio,bajo',
            'prioridad' => 'required|in:alta,media,baja'
        ];

        if (!$validator->validate($rules)) {
            $this->json(['success' => false, 'errors' => $validator->errors()], 400);
            return;
        }

        $gapAnterior = $this->gapRepo->findWithDetails((int)$id);

        if (!$gapAnterior) {
            $this->json(['success' => false, 'error' => 'GAP no encontrado'], 404);
            return;
        }

        $gapModel = new Gap();
        $result = $gapModel->update((int)$id, [
            'brecha' => $request->post('brecha'),
            'impacto' => $request->post('impacto'),
            'prioridad' => $request->post('prioridad'),
            'fecha_objetivo' => $request->post('fecha_objetivo')
        ]);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'gap_items',
                (int)$id,
                [
                    'brecha' => $gapAnterior['brecha'],
                    'impacto' => $gapAnterior['impacto'],
                    'prioridad' => $gapAnterior['prioridad']
                ],
                [
                    'brecha' => $request->post('brecha'),
                    'impacto' => $request->post('impacto'),
                    'prioridad' => $request->post('prioridad')
                ]
            );

            $this->json(['success' => true, 'message' => 'GAP actualizado']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar'], 500);
        }
    }

    public function delete(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.delete')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $gapAnterior = $this->gapRepo->findWithDetails((int)$id);

        if (!$gapAnterior) {
            $this->json(['success' => false, 'error' => 'GAP no encontrado'], 404);
            return;
        }

        try {
            $this->gapRepo->beginTransaction();

            $db = \App\Core\Database::getInstance()->getConnection();
            $stmtAcciones = $db->prepare("UPDATE acciones SET estado_accion = 'eliminado' WHERE gap_id = :gap_id");
            $stmtAcciones->bindValue(':gap_id', (int)$id, \PDO::PARAM_INT);
            $stmtAcciones->execute();

            $gapModel = new Gap();
            $result = $gapModel->softDelete((int)$id);

            if ($result) {
                $this->auditService->log(
                    'DELETE',
                    'gap_items',
                    (int)$id,
                    [
                        'brecha' => $gapAnterior['brecha'],
                        'estado_gap' => $gapAnterior['estado_gap']
                    ],
                    null
                );

                $this->gapRepo->commit();
                $this->json(['success' => true, 'message' => 'GAP eliminado']);
            } else {
                $this->gapRepo->rollback();
                $this->json(['success' => false, 'error' => 'Error al eliminar'], 500);
            }
        } catch (\Exception $e) {
            $this->gapRepo->rollback();
            $this->json(['success' => false, 'error' => 'Error al eliminar'], 500);
        }
    }

    public function updateAccion(Request $request, Response $response, string $accionId): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $estado = $request->post('estado');
        $notas = $request->post('notas');

        if (!in_array($estado, ['pendiente', 'en_proceso', 'completada', 'vencida'])) {
            $this->json(['success' => false, 'error' => 'Estado invalido'], 400);
            return;
        }

        $accionModel = new Accion();
        $accionAnterior = $accionModel->find((int)$accionId);

        if (!$accionAnterior) {
            $this->json(['success' => false, 'error' => 'Accion no encontrada'], 404);
            return;
        }

        $data = [
            'estado' => $estado,
            'notas' => $notas
        ];

        if ($estado === 'completada') {
            $data['fecha_completado'] = date('Y-m-d');
        }

        $result = $accionModel->update((int)$accionId, $data);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'acciones',
                (int)$accionId,
                [
                    'estado' => $accionAnterior['estado'],
                    'notas' => $accionAnterior['notas']
                ],
                [
                    'estado' => $estado,
                    'notas' => $notas
                ]
            );

            $this->recalcularAvanceGap($accionAnterior['gap_id']);
            $this->json(['success' => true, 'message' => 'Accion actualizada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar'], 500);
        }
    }

    public function completarAccion(Request $request, Response $response, string $accionId): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('gaps.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $accionModel = new Accion();
        $accionAnterior = $accionModel->find((int)$accionId);

        if (!$accionAnterior) {
            $this->json(['success' => false, 'error' => 'Accion no encontrada'], 404);
            return;
        }

        $result = $accionModel->completar((int)$accionId);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'acciones',
                (int)$accionId,
                [
                    'estado' => $accionAnterior['estado']
                ],
                [
                    'estado' => 'completada',
                    'fecha_completado' => date('Y-m-d')
                ]
            );

            $this->recalcularAvanceGap($accionAnterior['gap_id']);
            $this->json(['success' => true, 'message' => 'Accion completada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al completar accion'], 500);
        }
    }

    private function recalcularAvanceGap(int $gapId): void
    {
        $db = \App\Core\Database::getInstance()->getConnection();

        $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as completadas
                FROM acciones
                WHERE gap_id = :gap_id AND estado_accion = 'activo'";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':gap_id', $gapId, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result && $result['total'] > 0) {
            $avance = round(($result['completadas'] / $result['total']) * 100, 2);
        } else {
            $avance = 0;
        }

        $updateSql = "UPDATE gap_items SET avance = :avance";
        $params = [':avance' => $avance, ':gap_id' => $gapId];

        if ($avance >= 100) {
            $updateSql .= ", estado_certificacion = 'pendiente_evidencia'";
        }

        $updateSql .= " WHERE id = :gap_id";

        $stmtUpdate = $db->prepare($updateSql);
        foreach ($params as $key => $value) {
            $stmtUpdate->bindValue($key, $value);
        }
        $stmtUpdate->execute();
    }
}
