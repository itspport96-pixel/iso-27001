<?php

namespace App\Services;

use App\Core\Database;
use App\Services\MailService;
use App\Services\ConfiguracionService;
use PDO;

class WorkflowService
{
    private PDO $db;
    private LogService $log;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->log = new LogService();
    }

    /**
     * Registra cambio de estado en el historial
     */
    public function registrarCambioEstado(
        string $tabla,
        int $registroId,
        ?string $estadoAnterior,
        string $estadoNuevo,
        int $usuarioId,
        ?string $comentarios = null
    ): bool {
        $tablaHistorial = $tabla . '_historial';
        $campoFk = rtrim($tabla, 's') . '_id';

        // Verificar si la tabla de historial existe
        $checkTable = $this->db->query("SHOW TABLES LIKE '{$tablaHistorial}'");
        if ($checkTable->rowCount() === 0) {
            $this->log->warning("Tabla de historial no existe: {$tablaHistorial}");
            return false;
        }

        try {
            $sql = "INSERT INTO {$tablaHistorial} 
                    ({$campoFk}, estado_anterior, estado_nuevo, comentarios, usuario_id) 
                    VALUES (:registro_id, :estado_anterior, :estado_nuevo, :comentarios, :usuario_id)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':registro_id', $registroId, PDO::PARAM_INT);
            $stmt->bindValue(':estado_anterior', $estadoAnterior);
            $stmt->bindValue(':estado_nuevo', $estadoNuevo);
            $stmt->bindValue(':comentarios', $comentarios);
            $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (\Exception $e) {
            $this->log->error('Error registrando cambio de estado', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obtiene historial de cambios de un registro
     */
    public function getHistorial(string $tabla, int $registroId): array
    {
        $tablaHistorial = $tabla . '_historial';
        $campoFk = rtrim($tabla, 's') . '_id';

        try {
            $sql = "SELECT h.*, u.nombre as usuario_nombre 
                    FROM {$tablaHistorial} h
                    INNER JOIN usuarios u ON h.usuario_id = u.id
                    WHERE h.{$campoFk} = :registro_id
                    ORDER BY h.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':registro_id', $registroId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Envía notificación de cambio de estado de evidencia
     */
    public function notificarCambioEstadoEvidencia(
        int $empresaId,
        int $evidenciaId,
        string $nuevoEstado,
        ?string $comentarios = null
    ): bool {
        try {
            // Obtener datos de la evidencia y usuario que la subió
            $sql = "SELECT e.*, u.nombre as usuario_nombre, u.email as usuario_email,
                           c.codigo as control_codigo, c.nombre as control_nombre
                    FROM evidencias e
                    INNER JOIN usuarios u ON e.subido_por = u.id
                    INNER JOIN controles c ON e.control_id = c.id
                    WHERE e.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $evidenciaId, PDO::PARAM_INT);
            $stmt->execute();
            $evidencia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evidencia || empty($evidencia['usuario_email'])) {
                return false;
            }

            $configService = new ConfiguracionService($empresaId);
            $smtpConfig = $configService->getSmtp();
            
            if (empty($smtpConfig['smtp_activo']) || $smtpConfig['smtp_activo'] != '1') {
                return false;
            }

            $mailService = new MailService($smtpConfig);
            $empresaData = $configService->getEmpresa();
            $empresaNombre = $empresaData['nombre'] ?? 'ISO 27001 Platform';

            $estadoTexto = match($nuevoEstado) {
                'aprobada' => 'APROBADA',
                'rechazada' => 'RECHAZADA',
                'pendiente' => 'PENDIENTE DE REVISIÓN',
                default => strtoupper($nuevoEstado)
            };

            $colorEstado = match($nuevoEstado) {
                'aprobada' => '#28a745',
                'rechazada' => '#dc3545',
                default => '#ffc107'
            };

            $asunto = "Evidencia {$estadoTexto} - {$empresaNombre}";
            
            $cuerpo = "
            <html>
            <body style='font-family: Arial, sans-serif; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto;'>
                    <div style='background: {$colorEstado}; color: white; padding: 20px; text-align: center;'>
                        <h1 style='margin: 0;'>Evidencia {$estadoTexto}</h1>
                    </div>
                    <div style='padding: 20px;'>
                        <p>Hola <strong>{$evidencia['usuario_nombre']}</strong>,</p>
                        <p>Tu evidencia ha sido revisada y su estado ha cambiado a: <strong style='color: {$colorEstado};'>{$estadoTexto}</strong></p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Control</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>{$evidencia['control_codigo']} - {$evidencia['control_nombre']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Archivo</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>{$evidencia['nombre_archivo']}</td>
                            </tr>
                            " . ($comentarios ? "<tr>
                                <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Comentarios</strong></td>
                                <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($comentarios) . "</td>
                            </tr>" : "") . "
                        </table>
                        
                        " . ($nuevoEstado === 'rechazada' ? "<p style='color: #dc3545;'><strong>Acción requerida:</strong> Por favor revisa los comentarios y sube una nueva evidencia corregida.</p>" : "") . "
                    </div>
                    <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
                        <p>Este es un mensaje automatico del sistema {$empresaNombre}.</p>
                    </div>
                </div>
            </body>
            </html>";

            return $mailService->enviar($evidencia['usuario_email'], $asunto, $cuerpo);

        } catch (\Exception $e) {
            $this->log->error('Error notificando cambio estado evidencia', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Notifica a validadores sobre nueva evidencia subida
     */
    public function notificarNuevaEvidencia(int $empresaId, int $evidenciaId): bool
    {
        try {
            // Obtener datos de la evidencia
            $sql = "SELECT e.*, u.nombre as subido_por_nombre,
                           c.codigo as control_codigo, c.nombre as control_nombre
                    FROM evidencias e
                    INNER JOIN usuarios u ON e.subido_por = u.id
                    INNER JOIN controles c ON e.control_id = c.id
                    WHERE e.id = :id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $evidenciaId, PDO::PARAM_INT);
            $stmt->execute();
            $evidencia = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$evidencia) {
                return false;
            }

            $configService = new ConfiguracionService($empresaId);
            $smtpConfig = $configService->getSmtp();
            
            if (empty($smtpConfig['smtp_activo']) || $smtpConfig['smtp_activo'] != '1') {
                return false;
            }

            // Obtener validadores (auditores y admins)
            $sqlValidadores = "SELECT id, nombre, email FROM usuarios 
                               WHERE empresa_id = :empresa_id 
                               AND rol IN ('admin_empresa', 'auditor') 
                               AND estado = 'activo' 
                               AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sqlValidadores);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $validadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($validadores)) {
                return false;
            }

            $mailService = new MailService($smtpConfig);
            $empresaData = $configService->getEmpresa();
            $empresaNombre = $empresaData['nombre'] ?? 'ISO 27001 Platform';

            $enviados = 0;
            foreach ($validadores as $validador) {
                $asunto = "Nueva evidencia pendiente de validación - {$empresaNombre}";
                
                $cuerpo = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto;'>
                        <div style='background: #17a2b8; color: white; padding: 20px; text-align: center;'>
                            <h1 style='margin: 0;'>Nueva Evidencia</h1>
                        </div>
                        <div style='padding: 20px;'>
                            <p>Hola <strong>{$validador['nombre']}</strong>,</p>
                            <p>Se ha subido una nueva evidencia que requiere tu validación:</p>
                            
                            <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                                <tr>
                                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Control</strong></td>
                                    <td style='padding: 8px; border: 1px solid #ddd;'>{$evidencia['control_codigo']} - {$evidencia['control_nombre']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Archivo</strong></td>
                                    <td style='padding: 8px; border: 1px solid #ddd;'>{$evidencia['nombre_archivo']}</td>
                                </tr>
                                <tr>
                                    <td style='padding: 8px; border: 1px solid #ddd; background: #f8f9fa;'><strong>Subido por</strong></td>
                                    <td style='padding: 8px; border: 1px solid #ddd;'>{$evidencia['subido_por_nombre']}</td>
                                </tr>
                            </table>
                            
                            <p>Por favor ingresa al sistema para revisar y validar esta evidencia.</p>
                        </div>
                    </div>
                </body>
                </html>";

                if ($mailService->enviar($validador['email'], $asunto, $cuerpo)) {
                    $enviados++;
                }
            }

            return $enviados > 0;

        } catch (\Exception $e) {
            $this->log->error('Error notificando nueva evidencia', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
