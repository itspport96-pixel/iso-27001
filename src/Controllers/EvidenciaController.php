<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Repositories\EvidenciaRepository;
use App\Repositories\ControlRepository;
use App\Repositories\RequerimientoRepository;
use App\Services\AuditService;
use App\Services\WorkflowService;
use App\Models\Evidencia;
use App\Services\FileService;
use App\Middleware\RoleMiddleware;

class EvidenciaController extends Controller
{
    private EvidenciaRepository $evidenciaRepo;
    private ControlRepository $controlRepo;
    private RequerimientoRepository $requerimientoRepo;
    private FileService $fileService;
    private AuditService $auditService;
    private WorkflowService $workflowService;

    public function __construct()
    {
        parent::__construct();
        $this->evidenciaRepo     = new EvidenciaRepository();
        $this->controlRepo       = new ControlRepository();
        $this->requerimientoRepo = new RequerimientoRepository();
        $this->fileService       = new FileService();
        $this->auditService      = new AuditService();
        $this->workflowService   = new WorkflowService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('evidencias.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $estado = $request->get('estado');

        if ($estado) {
            $evidencias = $this->evidenciaRepo->getByEstadoValidacion($estado);
        } else {
            $evidencias = $this->evidenciaRepo->getWithControlInfo();
        }

        $estadisticas = $this->evidenciaRepo->getEstadisticas();

        $this->view('evidencias/index', [
            'evidencias'     => $evidencias,
            'estadisticas'   => $estadisticas,
            'filtro_estado'  => $estado,
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('evidencias.upload')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        // Solo mostramos controles aplicables en el formulario
        $controles = $this->controlRepo->getAplicables();
        $controlIdPreseleccionado = $request->get('control_id');

        $this->view('evidencias/create', [
            'controles'              => $controles,
            'control_preseleccionado' => $controlIdPreseleccionado,
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->request  = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('evidencias.upload')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        $userId    = $this->user()['id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());

        $rules = [
            'control_id' => 'required|numeric',
        ];

        if (!$validator->validate($rules)) {
            $this->session->flash('error', 'Control invalido');
            $response->redirect('/evidencias/create');
            return;
        }

        $controlId = (int)$request->post('control_id');

        // Validar que el control sea aplicable
        if (!$this->evidenciaRepo->controlEsAplicable($controlId)) {
            $this->session->flash('error', 'No se puede subir evidencia a un control marcado como no aplicable.');
            $response->redirect('/evidencias/create');
            return;
        }

        $file = $request->file('archivo');

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->flash('error', 'Debe seleccionar un archivo valido');
            $response->redirect('/evidencias/create');
            return;
        }

        try {
            $uploadResult = $this->fileService->upload($file, $empresaId);

            if (!$uploadResult['success']) {
                $this->session->flash('error', $uploadResult['error']);
                $response->redirect('/evidencias/create');
                return;
            }

            $evidenciaModel = new Evidencia();
            $evidenciaId    = $evidenciaModel->create([
                'control_id'         => $controlId,
                'nombre_archivo'     => $uploadResult['nombre_original'],
                'ruta_archivo'       => $uploadResult['ruta'],
                'tipo_mime'          => $uploadResult['tipo_mime'],
                'tamano'             => $uploadResult['tamano'],
                'hash_sha256'        => $uploadResult['hash'],
                'estado_validacion'  => 'pendiente',
                'subido_por'         => $userId,
                'comentarios'        => $request->post('comentarios'),
            ]);

            if ($evidenciaId) {
                $this->auditService->log(
                    'INSERT',
                    'evidencias',
                    $evidenciaId,
                    null,
                    [
                        'control_id'        => $controlId,
                        'nombre_archivo'    => $uploadResult['nombre_original'],
                        'estado_validacion' => 'pendiente',
                    ]
                );

                $this->session->flash('success', 'Evidencia subida exitosamente');
                $response->redirect('/evidencias');
            } else {
                $this->session->flash('error', 'Error al guardar evidencia');
                $response->redirect('/evidencias/create');
            }

        } catch (\Exception $e) {
            $this->session->flash('error', 'Error al subir evidencia: ' . $e->getMessage());
            $response->redirect('/evidencias/create');
        }
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request  = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('evidencias.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidencia = $this->evidenciaRepo->findWithDetails((int)$id);

        if (!$evidencia) {
            $this->response->error('Evidencia no encontrada', 404);
            return;
        }

        $this->view('evidencias/show', [
            'evidencia' => $evidencia,
        ]);
    }

    public function validar(Request $request, Response $response, string $id): void
    {
        $this->request  = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('evidencias.validate')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        $userId    = $this->user()['id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidenciaAnterior = $this->evidenciaRepo->findWithDetails((int)$id);

        if (!$evidenciaAnterior) {
            $this->json(['success' => false, 'error' => 'Evidencia no encontrada'], 404);
            return;
        }

        if ($evidenciaAnterior['estado_validacion'] !== 'pendiente') {
            $this->json(['success' => false, 'error' => 'Solo se puede validar evidencias pendientes'], 400);
            return;
        }

        $estado      = $request->post('estado_validacion');
        $comentarios = $request->post('comentarios');

        if (!in_array($estado, ['aprobada', 'rechazada'])) {
            $this->json(['success' => false, 'error' => 'Estado invalido'], 400);
            return;
        }

        try {
            $db = \App\Core\Database::getInstance()->getConnection();
            $db->beginTransaction();

            $evidenciaModel = new Evidencia();
            $result         = $evidenciaModel->validar((int)$id, $estado, $comentarios, $userId);

            if (!$result) {
                throw new \Exception('Error al validar evidencia');
            }

            if ($estado === 'aprobada') {
                $controlId = $evidenciaAnterior['control_id'];

                // Solo actualizar SOA a implementado si TODAS las evidencias
                // del control estan aprobadas (ninguna pendiente)
                if ($this->evidenciaRepo->todasEvidenciasAprobadas($controlId)) {
                    $stmtSoa = $db->prepare(
                        "SELECT id FROM soa_entries
                         WHERE control_id = :control_id
                         AND empresa_id = :empresa_id
                         LIMIT 1"
                    );
                    $stmtSoa->execute([
                        ':control_id' => $controlId,
                        ':empresa_id' => $empresaId,
                    ]);
                    $soa = $stmtSoa->fetch(\PDO::FETCH_ASSOC);

                    if ($soa) {
                        $stmtUpdateSoa = $db->prepare(
                            "UPDATE soa_entries
                             SET estado = 'implementado',
                                 fecha_evaluacion = CURDATE()
                             WHERE id = :soa_id
                             AND empresa_id = :empresa_id"
                        );
                        $stmtUpdateSoa->execute([
                            ':soa_id'     => $soa['id'],
                            ':empresa_id' => $empresaId,
                        ]);

                        // Cerrar GAPs activos del control si los hay
                        $stmtGap = $db->prepare(
                            "UPDATE gap_items
                             SET estado_certificacion = 'cerrado',
                                 fecha_real_cierre = CURDATE()
                             WHERE soa_id = :soa_id
                             AND estado_certificacion = 'pendiente_evidencia'
                             AND estado_gap = 'activo'
                             AND empresa_id = :empresa_id"
                        );
                        $stmtGap->execute([
                            ':soa_id'     => $soa['id'],
                            ':empresa_id' => $empresaId,
                        ]);
                    }
                }

                // Verificar completitud de requerimientos
                $this->requerimientoRepo->verificarTodosLosRequerimientos();
            }

            $db->commit();

            $this->auditService->log(
                'UPDATE',
                'evidencias',
                (int)$id,
                ['estado_validacion' => $evidenciaAnterior['estado_validacion']],
                [
                    'estado_validacion' => $estado,
                    'validado_por'      => $userId,
                    'comentarios'       => $comentarios,
                ]
            );

            $this->json(['success' => true, 'message' => 'Evidencia validada']);

        } catch (\Exception $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            $this->json(['success' => false, 'error' => 'Error al validar: ' . $e->getMessage()], 500);
        }
    }

    public function delete(Request $request, Response $response, string $id): void
    {
        $this->request  = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('evidencias.delete')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidenciaModel = new Evidencia();
        $evidencia      = $evidenciaModel->find((int)$id);

        if (!$evidencia) {
            $this->json(['success' => false, 'error' => 'Evidencia no encontrada'], 404);
            return;
        }

        if ($evidencia['estado_validacion'] === 'aprobada') {
            $this->json(['success' => false, 'error' => 'No se puede eliminar una evidencia aprobada. Las evidencias aprobadas son inmutables por cumplimiento normativo.'], 403);
            return;
        }

        if (file_exists($evidencia['ruta_archivo'])) {
            unlink($evidencia['ruta_archivo']);
        }

        $result = $evidenciaModel->delete((int)$id);

        if ($result) {
            $this->auditService->log(
                'DELETE',
                'evidencias',
                (int)$id,
                [
                    'nombre_archivo'    => $evidencia['nombre_archivo'],
                    'estado_validacion' => $evidencia['estado_validacion'],
                ],
                null
            );

            $this->json(['success' => true, 'message' => 'Evidencia eliminada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al eliminar'], 500);
        }
    }

    public function download(Request $request, Response $response, string $id): void
    {
        $this->request  = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('evidencias.view')) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidenciaModel = new Evidencia();
        $evidencia      = $evidenciaModel->find((int)$id);

        if (!$evidencia) {
            $this->response->error('Evidencia no encontrada', 404);
            return;
        }

        if (!file_exists($evidencia['ruta_archivo'])) {
            $this->response->error('Archivo no encontrado', 404);
            return;
        }

        $realPath   = realpath($evidencia['ruta_archivo']);
        $uploadBase = realpath($_ENV['UPLOAD_PATH'] ?? '/var/www/html/storage/uploads');

        if ($realPath === false || $uploadBase === false || strpos($realPath, $uploadBase) !== 0) {
            $this->response->error('Acceso denegado', 403);
            return;
        }

        header('Content-Type: ' . $evidencia['tipo_mime']);
        header('Content-Disposition: attachment; filename="' . basename($evidencia['nombre_archivo']) . '"');
        header('Content-Length: ' . filesize($evidencia['ruta_archivo']));
        readfile($evidencia['ruta_archivo']);
        exit;
    }
}
