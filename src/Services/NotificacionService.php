<?php

namespace App\Services;

use App\Core\Database;
use App\Services\MailService;
use App\Services\ConfiguracionService;
use PDO;

class NotificacionService
{
    private PDO $db;
    private LogService $log;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->log = new LogService();
    }

    /**
     * Obtiene acciones próximas a vencer (7, 3, 1 día)
     */
    public function getAccionesProximasVencer(int $empresaId, int $diasAnticipacion = 7): array
    {
        $sql = "SELECT a.*, g.brecha, u.nombre as responsable_nombre, u.email as responsable_email,
                       c.codigo as control_codigo, c.nombre as control_nombre,
                       DATEDIFF(a.fecha_compromiso, CURDATE()) as dias_restantes
                FROM acciones a
                INNER JOIN gap_items g ON a.gap_id = g.id
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                LEFT JOIN usuarios u ON a.responsable = u.nombre AND u.empresa_id = :empresa_id
                WHERE g.empresa_id = :empresa_id2
                AND a.estado IN ('pendiente', 'en_proceso')
                AND a.estado_accion = 'activo'
                AND a.fecha_compromiso BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                ORDER BY a.fecha_compromiso ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id2', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':dias', $diasAnticipacion, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene acciones vencidas
     */
    public function getAccionesVencidas(int $empresaId): array
    {
        $sql = "SELECT a.*, g.brecha, u.nombre as responsable_nombre, u.email as responsable_email,
                       c.codigo as control_codigo, c.nombre as control_nombre,
                       DATEDIFF(CURDATE(), a.fecha_compromiso) as dias_vencido
                FROM acciones a
                INNER JOIN gap_items g ON a.gap_id = g.id
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                LEFT JOIN usuarios u ON a.responsable = u.nombre AND u.empresa_id = :empresa_id
                WHERE g.empresa_id = :empresa_id2
                AND a.estado IN ('pendiente', 'en_proceso')
                AND a.estado_accion = 'activo'
                AND a.fecha_compromiso < CURDATE()
                ORDER BY a.fecha_compromiso ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id2', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene evidencias pendientes de validación
     */
    public function getEvidenciasPendientes(int $empresaId): array
    {
        $sql = "SELECT e.*, c.codigo as control_codigo, c.nombre as control_nombre,
                       u.nombre as subido_por_nombre, u.email as subido_por_email,
                       DATEDIFF(CURDATE(), e.created_at) as dias_pendiente
                FROM evidencias e
                INNER JOIN controles c ON e.control_id = c.id
                INNER JOIN usuarios u ON e.subido_por = u.id
                WHERE e.empresa_id = :empresa_id
                AND e.estado_validacion = 'pendiente'
                ORDER BY e.created_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene contraseñas próximas a expirar
     */
    public function getPasswordsProximasExpirar(int $empresaId, int $diasAnticipacion = 14): array
    {
        $sql = "SELECT id, nombre, email, password_updated_at,
                       DATEDIFF(DATE_ADD(password_updated_at, INTERVAL 90 DAY), CURDATE()) as dias_restantes
                FROM usuarios
                WHERE empresa_id = :empresa_id
                AND estado = 'activo'
                AND deleted_at IS NULL
                AND password_updated_at IS NOT NULL
                AND DATE_ADD(password_updated_at, INTERVAL 90 DAY) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :dias DAY)
                ORDER BY password_updated_at ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':dias', $diasAnticipacion, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los administradores de una empresa para notificar
     */
    public function getAdminsEmpresa(int $empresaId): array
    {
        $sql = "SELECT id, nombre, email 
                FROM usuarios 
                WHERE empresa_id = :empresa_id 
                AND rol = 'admin_empresa' 
                AND estado = 'activo' 
                AND deleted_at IS NULL";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Envía notificación de acciones próximas a vencer
     */
    public function enviarNotificacionesAcciones(int $empresaId): array
    {
        $resultados = ['enviados' => 0, 'errores' => 0, 'detalle' => []];
        
        try {
            $configService = new ConfiguracionService($empresaId);
            $smtpConfig = $configService->getSmtp();
            
            if (empty($smtpConfig['smtp_activo']) || $smtpConfig['smtp_activo'] != '1') {
                return ['enviados' => 0, 'errores' => 0, 'detalle' => ['SMTP no configurado']];
            }
            
            $mailService = new MailService($smtpConfig);
            $empresaData = $configService->getEmpresa();
            $empresaNombre = $empresaData['nombre'] ?? 'ISO 27001 Platform';
            
            // Acciones próximas a vencer
            $accionesProximas = $this->getAccionesProximasVencer($empresaId, 7);
            
            // Agrupar por responsable
            $porResponsable = [];
            foreach ($accionesProximas as $accion) {
                $email = $accion['responsable_email'] ?? null;
                if ($email) {
                    if (!isset($porResponsable[$email])) {
                        $porResponsable[$email] = [
                            'nombre' => $accion['responsable_nombre'],
                            'acciones' => []
                        ];
                    }
                    $porResponsable[$email]['acciones'][] = $accion;
                }
            }
            
            // Enviar email a cada responsable
            foreach ($porResponsable as $email => $data) {
                $asunto = "Recordatorio: Acciones próximas a vencer - {$empresaNombre}";
                $cuerpo = $this->generarEmailAccionesProximas($data['nombre'], $data['acciones'], $empresaNombre);
                
                if ($mailService->enviar($email, $asunto, $cuerpo)) {
                    $resultados['enviados']++;
                    $resultados['detalle'][] = "Enviado a {$email}";
                } else {
                    $resultados['errores']++;
                    $resultados['detalle'][] = "Error enviando a {$email}";
                }
            }
            
            // Notificar a admins sobre acciones vencidas
            $accionesVencidas = $this->getAccionesVencidas($empresaId);
            if (!empty($accionesVencidas)) {
                $admins = $this->getAdminsEmpresa($empresaId);
                foreach ($admins as $admin) {
                    $asunto = "ALERTA: Acciones vencidas - {$empresaNombre}";
                    $cuerpo = $this->generarEmailAccionesVencidas($admin['nombre'], $accionesVencidas, $empresaNombre);
                    
                    if ($mailService->enviar($admin['email'], $asunto, $cuerpo)) {
                        $resultados['enviados']++;
                    } else {
                        $resultados['errores']++;
                    }
                }
            }
            
        } catch (\Exception $e) {
            $this->log->error('Error enviando notificaciones', ['error' => $e->getMessage()]);
            $resultados['detalle'][] = 'Error: ' . $e->getMessage();
        }
        
        return $resultados;
    }

    /**
     * Envía notificación de evidencias pendientes de validación
     */
    public function enviarNotificacionesEvidencias(int $empresaId): array
    {
        $resultados = ['enviados' => 0, 'errores' => 0, 'detalle' => []];
        
        try {
            $configService = new ConfiguracionService($empresaId);
            $smtpConfig = $configService->getSmtp();
            
            if (empty($smtpConfig['smtp_activo']) || $smtpConfig['smtp_activo'] != '1') {
                return ['enviados' => 0, 'errores' => 0, 'detalle' => ['SMTP no configurado']];
            }
            
            $mailService = new MailService($smtpConfig);
            $empresaData = $configService->getEmpresa();
            $empresaNombre = $empresaData['nombre'] ?? 'ISO 27001 Platform';
            
            $evidencias = $this->getEvidenciasPendientes($empresaId);
            
            if (empty($evidencias)) {
                return ['enviados' => 0, 'errores' => 0, 'detalle' => ['No hay evidencias pendientes']];
            }
            
            // Notificar a auditores y admins
            $sql = "SELECT id, nombre, email FROM usuarios 
                    WHERE empresa_id = :empresa_id 
                    AND rol IN ('admin_empresa', 'auditor') 
                    AND estado = 'activo' 
                    AND deleted_at IS NULL";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
            $stmt->execute();
            $validadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($validadores as $validador) {
                $asunto = "Evidencias pendientes de validación - {$empresaNombre}";
                $cuerpo = $this->generarEmailEvidenciasPendientes($validador['nombre'], $evidencias, $empresaNombre);
                
                if ($mailService->enviar($validador['email'], $asunto, $cuerpo)) {
                    $resultados['enviados']++;
                } else {
                    $resultados['errores']++;
                }
            }
            
        } catch (\Exception $e) {
            $this->log->error('Error enviando notificaciones evidencias', ['error' => $e->getMessage()]);
        }
        
        return $resultados;
    }

    /**
     * Envía todas las notificaciones pendientes
     */
    public function enviarTodasNotificaciones(int $empresaId): array
    {
        $resultados = [
            'acciones' => $this->enviarNotificacionesAcciones($empresaId),
            'evidencias' => $this->enviarNotificacionesEvidencias($empresaId),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->log->info('Notificaciones enviadas', [
            'empresa_id' => $empresaId,
            'resultados' => $resultados
        ]);
        
        return $resultados;
    }

    /**
     * Genera el HTML del email de acciones próximas a vencer
     */
    private function generarEmailAccionesProximas(string $nombre, array $acciones, string $empresaNombre): string
    {
        $filas = '';
        foreach ($acciones as $a) {
            $diasClass = $a['dias_restantes'] <= 1 ? 'color: #e74c3c; font-weight: bold;' : 
                        ($a['dias_restantes'] <= 3 ? 'color: #f39c12;' : '');
            $filas .= "<tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$a['control_codigo']}</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars(substr($a['descripcion'], 0, 100)) . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$a['fecha_compromiso']}</td>
                <td style='padding: 8px; border: 1px solid #ddd; {$diasClass}'>{$a['dias_restantes']} dias</td>
            </tr>";
        }
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto;'>
                <div style='background: #3498db; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0;'>{$empresaNombre}</h1>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>Tienes <strong>" . count($acciones) . "</strong> accion(es) proxima(s) a vencer:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <thead>
                            <tr style='background: #f8f9fa;'>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Control</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Descripcion</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Fecha Limite</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Dias Restantes</th>
                            </tr>
                        </thead>
                        <tbody>{$filas}</tbody>
                    </table>
                    
                    <p>Por favor, revisa y completa estas acciones antes de su fecha limite.</p>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
                    <p>Este es un mensaje automatico del sistema ISO 27001 Compliance Platform.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Genera el HTML del email de acciones vencidas
     */
    private function generarEmailAccionesVencidas(string $nombre, array $acciones, string $empresaNombre): string
    {
        $filas = '';
        foreach ($acciones as $a) {
            $filas .= "<tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$a['control_codigo']}</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars(substr($a['descripcion'], 0, 100)) . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$a['fecha_compromiso']}</td>
                <td style='padding: 8px; border: 1px solid #ddd; color: #e74c3c; font-weight: bold;'>{$a['dias_vencido']} dias</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($a['responsable_nombre'] ?? 'Sin asignar') . "</td>
            </tr>";
        }
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto;'>
                <div style='background: #e74c3c; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0;'>ALERTA - {$empresaNombre}</h1>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p style='color: #e74c3c;'><strong>ATENCION:</strong> Hay <strong>" . count($acciones) . "</strong> accion(es) vencida(s) que requieren atencion inmediata:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <thead>
                            <tr style='background: #fee;'>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Control</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Descripcion</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Fecha Limite</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Dias Vencido</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>{$filas}</tbody>
                    </table>
                    
                    <p>Se recomienda tomar accion inmediata para regularizar estas actividades.</p>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
                    <p>Este es un mensaje automatico del sistema ISO 27001 Compliance Platform.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Genera el HTML del email de evidencias pendientes
     */
    private function generarEmailEvidenciasPendientes(string $nombre, array $evidencias, string $empresaNombre): string
    {
        $filas = '';
        foreach ($evidencias as $e) {
            $filas .= "<tr>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$e['control_codigo']}</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($e['nombre_archivo']) . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>" . htmlspecialchars($e['subido_por_nombre']) . "</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$e['created_at']}</td>
                <td style='padding: 8px; border: 1px solid #ddd;'>{$e['dias_pendiente']} dias</td>
            </tr>";
        }
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto;'>
                <div style='background: #f39c12; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0;'>{$empresaNombre}</h1>
                </div>
                <div style='padding: 20px;'>
                    <p>Hola <strong>{$nombre}</strong>,</p>
                    <p>Hay <strong>" . count($evidencias) . "</strong> evidencia(s) pendiente(s) de validacion:</p>
                    
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <thead>
                            <tr style='background: #fff8e1;'>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Control</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Archivo</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Subido por</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Fecha</th>
                                <th style='padding: 8px; border: 1px solid #ddd; text-align: left;'>Dias Pendiente</th>
                            </tr>
                        </thead>
                        <tbody>{$filas}</tbody>
                    </table>
                    
                    <p>Por favor, revisa y valida estas evidencias.</p>
                </div>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666;'>
                    <p>Este es un mensaje automatico del sistema ISO 27001 Compliance Platform.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}
