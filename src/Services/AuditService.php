<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Core\TenantContext;
use App\Core\Session;

class AuditService
{
    private AuditLog $auditLog;
    private Session $session;

    public function __construct()
    {
        $this->auditLog = new AuditLog();
        $this->session = new Session();
    }

    public function log(
        string $accion,
        string $tabla,
        ?int $registroId = null,
        ?array $datosPrevios = null,
        ?array $datosNuevos = null
    ): void {
        $empresaId = TenantContext::getInstance()->getTenant();
        $usuarioId = $this->session->get('user_id');

        if (!$empresaId || !$usuarioId) {
            return;
        }

        $this->auditLog->create([
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'accion' => strtoupper($accion),
            'tabla' => $tabla,
            'registro_id' => $registroId,
            'datos_previos' => $datosPrevios ? json_encode($datosPrevios) : null,
            'datos_nuevos' => $datosNuevos ? json_encode($datosNuevos) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    }

    public function logLogin(int $usuarioId, int $empresaId, bool $exitoso): void
    {
        try {
            $accion = $exitoso ? 'LOGIN' : 'LOGIN_FALLIDO';
            
            $db = \App\Core\Database::getInstance()->getConnection();
            
            $sql = "INSERT INTO audit_logs (empresa_id, usuario_id, accion, tabla, registro_id, ip, user_agent, created_at)
                    VALUES (:empresa_id, :usuario_id, :accion, 'usuarios', :registro_id, :ip, :user_agent, NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':empresa_id' => $empresaId,
                ':usuario_id' => $usuarioId,
                ':accion' => $accion,
                ':registro_id' => $usuarioId,
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (\Exception $e) {
            error_log("AuditService::logLogin ERROR: " . $e->getMessage());
        }
    }

    public function logLogout(): void
    {
        try {
            $empresaId = TenantContext::getInstance()->getTenant();
            $usuarioId = $this->session->get('user_id');

            if (!$empresaId || !$usuarioId) {
                return;
            }

            $this->auditLog->create([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'accion' => 'LOGOUT',
                'tabla' => 'usuarios',
                'registro_id' => $usuarioId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
            ]);
        } catch (\Exception $e) {
            error_log("AuditService::logLogout ERROR: " . $e->getMessage());
        }
    }
}
