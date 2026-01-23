<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Repositories\ControlRepository;
use App\Repositories\SOARepository;
use App\Middleware\AuthMiddleware;

class ControlController extends Controller
{
    private ControlRepository $controlRepo;
    private SOARepository $soaRepo;

    public function __construct()
    {
        parent::__construct();
        $this->controlRepo = new ControlRepository();
        $this->soaRepo = new SOARepository();
    }

    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

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

    public function show(Request $request, Response $response, string $id): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

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

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $estado = $request->post('estado');
        $justificacion = $request->post('justificacion');
        $aplicable = $request->post('aplicable');

        if (!in_array($estado, ['no_implementado', 'parcial', 'implementado'])) {
            $this->json(['success' => false, 'error' => 'Estado inválido'], 400);
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
