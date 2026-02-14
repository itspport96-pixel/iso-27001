<?php

namespace App\Controllers;

use App\Controllers\Base\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\TenantContext;
use App\Services\NotificacionService;
use App\Services\AuditService;
use App\Middleware\RoleMiddleware;

class NotificacionController extends Controller
{
    private NotificacionService $notificacionService;
    private AuditService $auditService;

    public function __construct()
    {
        parent::__construct();
        $this->notificacionService = new NotificacionService();
        $this->auditService = new AuditService();
    }

    /**
     * Vista principal de notificaciones
     */
    public function index(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('configuracion.view')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $this->view('notificaciones/index');
    }

    /**
     * Obtiene resumen de pendientes para notificar
     */
    public function getResumen(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $resumen = [
            'acciones_proximas' => $this->notificacionService->getAccionesProximasVencer($empresaId, 7),
            'acciones_vencidas' => $this->notificacionService->getAccionesVencidas($empresaId),
            'evidencias_pendientes' => $this->notificacionService->getEvidenciasPendientes($empresaId),
            'passwords_por_expirar' => $this->notificacionService->getPasswordsProximasExpirar($empresaId, 14)
        ];

        $this->json([
            'success' => true,
            'data' => $resumen,
            'contadores' => [
                'acciones_proximas' => count($resumen['acciones_proximas']),
                'acciones_vencidas' => count($resumen['acciones_vencidas']),
                'evidencias_pendientes' => count($resumen['evidencias_pendientes']),
                'passwords_por_expirar' => count($resumen['passwords_por_expirar'])
            ]
        ]);
    }

    /**
     * Envía notificaciones manualmente
     */
    public function enviar(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        if (!RoleMiddleware::can('configuracion.edit')) {
            $this->json(['success' => false, 'error' => 'Acceso denegado'], 403);
            return;
        }

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        $tipo = $request->post('tipo') ?? 'todas';

        try {
            $resultado = [];

            switch ($tipo) {
                case 'acciones':
                    $resultado = $this->notificacionService->enviarNotificacionesAcciones($empresaId);
                    break;
                case 'evidencias':
                    $resultado = $this->notificacionService->enviarNotificacionesEvidencias($empresaId);
                    break;
                case 'todas':
                default:
                    $resultado = $this->notificacionService->enviarTodasNotificaciones($empresaId);
                    break;
            }

            $this->auditService->log(
                'NOTIFICACION',
                'notificaciones',
                null,
                ['tipo' => $tipo],
                ['resultado' => $resultado]
            );

            $this->json([
                'success' => true,
                'message' => 'Notificaciones enviadas',
                'resultado' => $resultado
            ]);

        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => 'Error al enviar notificaciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene el historial de notificaciones enviadas
     */
    public function getHistorial(Request $request, Response $response): void
    {
        $this->request = $request;
        $this->response = $response;
        $this->requireAuth();

        $empresaId = $this->user()['empresa_id'];
        TenantContext::getInstance()->setTenant($empresaId);

        // Obtener del log de auditoría las últimas notificaciones enviadas
        $sql = "SELECT created_at, new_data 
                FROM audit_log 
                WHERE empresa_id = :empresa_id 
                AND tabla = 'notificaciones'
                AND accion = 'NOTIFICACION'
                ORDER BY created_at DESC
                LIMIT 20";

        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId);
        $stmt->execute();
        
        $historial = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->json([
            'success' => true,
            'data' => $historial
        ]);
    }
}
