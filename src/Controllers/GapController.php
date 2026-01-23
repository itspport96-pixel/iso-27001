<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Core\Validator;
use App\Repositories\GapRepository;
use App\Models\Gap;
use App\Models\Accion;

class GapController extends Controller
{
    private GapRepository $gapRepo;

    public function __construct()
    {
        parent::__construct();
        $this->gapRepo = new GapRepository();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

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
            $response->redirect('/gaps/create');
            return;
        }

        $soaId = (int)$request->post('soa_id');

        if (!$this->gapRepo->validarSoaPermiteGap($soaId)) {
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

            // Recibir arrays PHP directamente
            $descripciones = $_POST['accion_descripcion'] ?? [];
            $responsables = $_POST['accion_responsable'] ?? [];
            $fechas = $_POST['accion_fecha'] ?? [];
            
            $accionModel = new Accion();
            
            for ($i = 0; $i < count($descripciones); $i++) {
                if (!empty($descripciones[$i]) && !empty($fechas[$i])) {
                    $accionModel->create([
                        'gap_id' => $gapId,
                        'descripcion' => $descripciones[$i],
                        'responsable' => $responsables[$i] ?? null,
                        'fecha_compromiso' => $fechas[$i],
                        'estado' => 'pendiente'
                    ]);
                }
            }

            $this->gapRepo->commit();

            $response->redirect('/gaps/' . $gapId);

        } catch (\Exception $e) {
            $this->gapRepo->rollback();
            $response->redirect('/gaps/create');
        }
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

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

        $gapModel = new Gap();
        $result = $gapModel->update((int)$id, [
            'brecha' => $request->post('brecha'),
            'impacto' => $request->post('impacto'),
            'prioridad' => $request->post('prioridad'),
            'fecha_objetivo' => $request->post('fecha_objetivo')
        ]);

        if ($result) {
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

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $gapModel = new Gap();
        $result = $gapModel->softDelete((int)$id);

        if ($result) {
            $this->json(['success' => true, 'message' => 'GAP eliminado']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al eliminar'], 500);
        }
    }

    public function updateAccion(Request $request, Response $response, string $accionId): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $estado = $request->post('estado');
        $notas = $request->post('notas');

        if (!in_array($estado, ['pendiente', 'en_proceso', 'completada', 'vencida'])) {
            $this->json(['success' => false, 'error' => 'Estado inválido'], 400);
            return;
        }

        $data = [
            'estado' => $estado,
            'notas' => $notas
        ];

        if ($estado === 'completada') {
            $data['fecha_completado'] = date('Y-m-d');
        }

        $accionModel = new Accion();
        $result = $accionModel->update((int)$accionId, $data);

        if ($result) {
            $this->json(['success' => true, 'message' => 'Acción actualizada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al actualizar'], 500);
        }
    }

    public function completarAccion(Request $request, Response $response, string $accionId): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $accionModel = new Accion();
        $result = $accionModel->completar((int)$accionId);

        if ($result) {
            $this->json(['success' => true, 'message' => 'Acción completada']);
        } else {
            $this->json(['success' => false, 'error' => 'Error al completar acción'], 500);
        }
    }
}
