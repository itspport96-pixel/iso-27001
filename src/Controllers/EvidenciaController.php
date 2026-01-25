<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Repositories\EvidenciaRepository;
use App\Repositories\ControlRepository;
use App\Services\AuditService;
use App\Models\Evidencia;
use App\Services\FileService;

class EvidenciaController extends Controller
{
    private EvidenciaRepository $evidenciaRepo;
    private ControlRepository $controlRepo;
    private FileService $fileService;
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->evidenciaRepo = new EvidenciaRepository();
        $this->controlRepo = new ControlRepository();
        $this->fileService = new FileService();
        $this->auditService = new AuditService();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

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
            'evidencias' => $evidencias,
            'estadisticas' => $estadisticas,
            'filtro_estado' => $estado
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $controles = $this->controlRepo->getAllWithDominio();

        $this->view('evidencias/create', [
            'controles' => $controles
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        $userId = $this->user()['id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $validator = new Validator($request->all());
        
        $rules = [
            'control_id' => 'required|numeric'
        ];
        
        if (!$validator->validate($rules)) {
            $response->redirect('/evidencias/create');
            return;
        }

        $file = $request->file('archivo');
        
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->session->flash('error', 'Debe seleccionar un archivo válido');
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
            $evidenciaId = $evidenciaModel->create([
                'control_id' => (int)$request->post('control_id'),
                'nombre_archivo' => $uploadResult['nombre_original'],
                'ruta_archivo' => $uploadResult['ruta'],
                'tipo_mime' => $uploadResult['tipo_mime'],
                'tamano' => $uploadResult['tamano'],
                'hash_sha256' => $uploadResult['hash'],
                'estado_validacion' => 'pendiente',
                'subido_por' => $userId
            ]);

            if ($evidenciaId) {
                $this->auditService->log(
                    'INSERT',
                    'evidencias',
                    $evidenciaId,
                    null,
                    [
                        'control_id' => (int)$request->post('control_id'),
                        'nombre_archivo' => $uploadResult['nombre_original'],
                        'estado_validacion' => 'pendiente'
                    ]
                );

                $this->session->flash('success', 'Evidencia subida exitosamente');
                $response->redirect('/evidencias');
            } else {
                $response->redirect('/evidencias/create');
            }

        } catch (\Exception $e) {
            $this->session->flash('error', 'Error al subir evidencia: ' . $e->getMessage());
            $response->redirect('/evidencias/create');
        }
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidencia = $this->evidenciaRepo->findWithDetails((int)$id);

        if (!$evidencia) {
            $this->response->error('Evidencia no encontrada', 404);
            return;
        }

        $this->view('evidencias/show', [
            'evidencia' => $evidencia
        ]);
    }

    public function validar(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        $userId = $this->user()['id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidenciaAnterior = $this->evidenciaRepo->findWithDetails((int)$id);

        $estado = $request->post('estado_validacion');
        $comentarios = $request->post('comentarios');

        if (!in_array($estado, ['aprobada', 'rechazada'])) {
            $this->json(['success' => false, 'error' => 'Estado inválido'], 400);
            return;
        }

        $evidenciaModel = new Evidencia();
        $result = $evidenciaModel->validar((int)$id, $estado, $comentarios, $userId);

        if ($result) {
            $this->auditService->log(
                'UPDATE',
                'evidencias',
                (int)$id,
                [
                    'estado_validacion' => $evidenciaAnterior['estado_validacion']
                ],
                [
                    'estado_validacion' => $estado,
                    'validado_por' => $userId,
                    'comentarios' => $comentarios
                ]
            );

            $this->json(['success' => true, 'message' => 'Evidencia validada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al validar'], 500);
        }
    }

    public function delete(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidenciaModel = new Evidencia();
        $evidencia = $evidenciaModel->find((int)$id);

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
                    'nombre_archivo' => $evidencia['nombre_archivo'],
                    'estado_validacion' => $evidencia['estado_validacion']
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
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $evidenciaModel = new Evidencia();
        $evidencia = $evidenciaModel->find((int)$id);

        if (!$evidencia) {
            $this->response->error('Evidencia no encontrada', 404);
            return;
        }

        if (!file_exists($evidencia['ruta_archivo'])) {
            $this->response->error('Archivo no encontrado', 404);
            return;
        }

        header('Content-Type: ' . $evidencia['tipo_mime']);
        header('Content-Disposition: attachment; filename="' . $evidencia['nombre_archivo'] . '"');
        header('Content-Length: ' . filesize($evidencia['ruta_archivo']));
        readfile($evidencia['ruta_archivo']);
        exit;
    }
}
