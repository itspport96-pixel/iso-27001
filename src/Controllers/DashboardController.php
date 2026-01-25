<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Repositories\SOARepository;
use App\Repositories\GapRepository;
use App\Repositories\EvidenciaRepository;
use App\Repositories\RequerimientoRepository;

class DashboardController extends Controller
{
    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        // Obtener métricas de todos los módulos
        $soaRepo = new SOARepository();
        $gapRepo = new GapRepository();
        $evidenciaRepo = new EvidenciaRepository();
        $requerimientoRepo = new RequerimientoRepository();

        $metricas = [
            'controles' => $soaRepo->getEstadisticas(),
            'controles_por_dominio' => $soaRepo->getEstadisticasPorDominio(),
            'gaps' => $gapRepo->getEstadisticas(),
            'evidencias' => $evidenciaRepo->getEstadisticas(),
            'requerimientos' => $requerimientoRepo->getEstadisticas(),
            'user' => $this->user()
        ];

        $this->view('layouts/dashboard', $metricas);
    }
}
