<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Services\ReporteService;
use App\Services\ConfiguracionService;
use App\Services\AuditService;
use App\Middleware\RoleMiddleware;

class ReporteController extends Controller
{
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->auditService = new AuditService();
    }

    /**
     * Vista principal de reportes
     */
    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $this->view('reportes/index');
    }

    /**
     * Genera y descarga el SOA en HTML/PDF
     */
    public function descargarSOA(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $configService = new ConfiguracionService($empresaId);
        $empresa = $configService->getEmpresa();
        
        $reporteService = new ReporteService($empresaId);
        $html = $reporteService->generarHTMLSOA($empresa);

        $this->auditService->log('EXPORT', 'reportes', null, [], ['tipo' => 'soa_html']);

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="SOA_' . date('Y-m-d') . '.html"');
        echo $html;
        exit;
    }

    /**
     * Genera y descarga GAPs en HTML/PDF
     */
    public function descargarGAPs(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $configService = new ConfiguracionService($empresaId);
        $empresa = $configService->getEmpresa();
        
        $reporteService = new ReporteService($empresaId);
        $html = $reporteService->generarHTMLGAPs($empresa);

        $this->auditService->log('EXPORT', 'reportes', null, [], ['tipo' => 'gaps_html']);

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="GAPs_' . date('Y-m-d') . '.html"');
        echo $html;
        exit;
    }

    /**
     * Genera y descarga Reporte Ejecutivo en HTML/PDF
     */
    public function descargarEjecutivo(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $configService = new ConfiguracionService($empresaId);
        $empresa = $configService->getEmpresa();
        
        $reporteService = new ReporteService($empresaId);
        $html = $reporteService->generarHTMLEjecutivo($empresa);

        $this->auditService->log('EXPORT', 'reportes', null, [], ['tipo' => 'ejecutivo_html']);

        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="Reporte_Ejecutivo_' . date('Y-m-d') . '.html"');
        echo $html;
        exit;
    }

    /**
     * Exporta SOA a CSV/Excel
     */
    public function exportarSOAExcel(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $reporteService = new ReporteService($empresaId);
        $datos = $reporteService->getSOACompleto();

        $cabeceras = [
            'codigo' => 'Código',
            'control_nombre' => 'Control',
            'dominio' => 'Dominio',
            'aplicabilidad' => 'Aplicabilidad',
            'justificacion' => 'Justificación',
            'estado_implementacion' => 'Estado',
            'responsable' => 'Responsable',
            'fecha_revision' => 'Fecha Revisión'
        ];

        $csv = $reporteService->generarCSV($datos, $cabeceras);

        $this->auditService->log('EXPORT', 'reportes', null, [], ['tipo' => 'soa_csv']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="SOA_' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit;
    }

    /**
     * Exporta GAPs a CSV/Excel
     */
    public function exportarGAPsExcel(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $reporteService = new ReporteService($empresaId);
        $datos = $reporteService->getGAPsYAcciones();

        $cabeceras = [
            'control_codigo' => 'Control',
            'brecha' => 'Brecha',
            'riesgo_asociado' => 'Riesgo',
            'prioridad' => 'Prioridad',
            'gap_estado' => 'Estado GAP',
            'accion_descripcion' => 'Acción',
            'responsable' => 'Responsable',
            'fecha_compromiso' => 'Fecha Límite',
            'accion_estado' => 'Estado Acción',
            'avance' => 'Avance %'
        ];

        $csv = $reporteService->generarCSV($datos, $cabeceras);

        $this->auditService->log('EXPORT', 'reportes', null, [], ['tipo' => 'gaps_csv']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="GAPs_Acciones_' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit;
    }

    /**
     * Exporta Evidencias a CSV/Excel
     */
    public function exportarEvidenciasExcel(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $reporteService = new ReporteService($empresaId);
        $datos = $reporteService->getEvidencias();

        $cabeceras = [
            'control_codigo' => 'Control',
            'nombre_archivo' => 'Archivo',
            'descripcion' => 'Descripción',
            'tipo_archivo' => 'Tipo',
            'estado_validacion' => 'Estado',
            'subido_por_nombre' => 'Subido Por',
            'created_at' => 'Fecha Subida',
            'validado_por' => 'Validado Por',
            'fecha_validacion' => 'Fecha Validación'
        ];

        $csv = $reporteService->generarCSV($datos, $cabeceras);

        $this->auditService->log('EXPORT', 'reportes', null, [], ['tipo' => 'evidencias_csv']);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Evidencias_' . date('Y-m-d') . '.csv"');
        echo $csv;
        exit;
    }

    /**
     * Vista previa del reporte (para imprimir como PDF)
     */
    public function vistaPrevia(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        $tipo = $request->get('tipo') ?? 'ejecutivo';
        
        TenantContext::getInstance()->setTenant($empresaId);

        $configService = new ConfiguracionService($empresaId);
        $empresa = $configService->getEmpresa();
        
        $reporteService = new ReporteService($empresaId);

        $html = match($tipo) {
            'soa' => $reporteService->generarHTMLSOA($empresa),
            'gaps' => $reporteService->generarHTMLGAPs($empresa),
            default => $reporteService->generarHTMLEjecutivo($empresa)
        };

        echo $html;
        exit;
    }
}
