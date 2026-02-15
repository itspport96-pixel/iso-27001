<?php

namespace App\Services;

use App\Core\Database;
use App\Core\TenantContext;
use PDO;

class ReporteService
{
    private PDO $db;
    private int $empresaId;

    public function __construct(int $empresaId)
    {
        $this->db = Database::getInstance()->getConnection();
        $this->empresaId = $empresaId;
    }

    /**
     * Genera datos del SOA completo
     */
    public function getSOACompleto(): array
    {
        $sql = "SELECT 
                    c.codigo, c.nombre as control_nombre, c.descripcion as control_descripcion,
                    d.nombre as dominio,
                    s.aplicabilidad, s.justificacion, s.estado_implementacion,
                    s.responsable, s.fecha_revision
                FROM controles c
                INNER JOIN dominios d ON c.dominio_id = d.id
                LEFT JOIN soa_entries s ON c.id = s.control_id AND s.empresa_id = :empresa_id
                ORDER BY c.codigo";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Genera datos de GAPs y acciones
     */
    public function getGAPsYAcciones(): array
    {
        $sql = "SELECT 
                    g.id, g.brecha, g.riesgo_asociado, g.prioridad, g.estado as gap_estado,
                    c.codigo as control_codigo, c.nombre as control_nombre,
                    a.descripcion as accion_descripcion, a.responsable, 
                    a.fecha_compromiso, a.estado as accion_estado, a.avance
                FROM gap_items g
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                LEFT JOIN acciones a ON a.gap_id = g.id AND a.estado_accion = 'activo'
                WHERE g.empresa_id = :empresa_id
                ORDER BY c.codigo, g.prioridad DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Genera datos de evidencias
     */
    public function getEvidencias(): array
    {
        $sql = "SELECT 
                    e.id, e.nombre_archivo, e.descripcion, e.tipo_archivo, e.tamano,
                    e.estado_validacion, e.validado_por, e.fecha_validacion,
                    e.created_at,
                    c.codigo as control_codigo, c.nombre as control_nombre,
                    u.nombre as subido_por_nombre
                FROM evidencias e
                INNER JOIN controles c ON e.control_id = c.id
                INNER JOIN usuarios u ON e.subido_por = u.id
                WHERE e.empresa_id = :empresa_id
                ORDER BY c.codigo, e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Genera resumen ejecutivo de cumplimiento
     */
    public function getResumenCumplimiento(): array
    {
        // Total controles
        $sqlTotal = "SELECT COUNT(*) FROM controles";
        $total = $this->db->query($sqlTotal)->fetchColumn();

        // Controles aplicables
        $sqlAplicables = "SELECT COUNT(*) FROM soa_entries WHERE empresa_id = :empresa_id AND aplicabilidad = 'aplica'";
        $stmt = $this->db->prepare($sqlAplicables);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        $aplicables = $stmt->fetchColumn();

        // Por estado de implementación
        $sqlEstados = "SELECT estado_implementacion, COUNT(*) as cantidad 
                       FROM soa_entries 
                       WHERE empresa_id = :empresa_id AND aplicabilidad = 'aplica'
                       GROUP BY estado_implementacion";
        $stmt = $this->db->prepare($sqlEstados);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        $estados = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // GAPs abiertos
        $sqlGaps = "SELECT COUNT(*) FROM gap_items WHERE empresa_id = :empresa_id AND estado != 'cerrado'";
        $stmt = $this->db->prepare($sqlGaps);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        $gapsAbiertos = $stmt->fetchColumn();

        // Acciones pendientes
        $sqlAcciones = "SELECT COUNT(*) FROM acciones a 
                        INNER JOIN gap_items g ON a.gap_id = g.id 
                        WHERE g.empresa_id = :empresa_id AND a.estado IN ('pendiente', 'en_proceso')";
        $stmt = $this->db->prepare($sqlAcciones);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        $accionesPendientes = $stmt->fetchColumn();

        // Evidencias
        $sqlEvidencias = "SELECT estado_validacion, COUNT(*) as cantidad 
                          FROM evidencias 
                          WHERE empresa_id = :empresa_id
                          GROUP BY estado_validacion";
        $stmt = $this->db->prepare($sqlEvidencias);
        $stmt->bindValue(':empresa_id', $this->empresaId, PDO::PARAM_INT);
        $stmt->execute();
        $evidencias = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Calcular porcentaje de cumplimiento
        $implementados = ($estados['implementado'] ?? 0) + ($estados['parcial'] ?? 0) * 0.5;
        $porcentajeCumplimiento = $aplicables > 0 ? round(($implementados / $aplicables) * 100, 1) : 0;

        return [
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'total_controles' => $total,
            'controles_aplicables' => $aplicables,
            'estados_implementacion' => $estados,
            'gaps_abiertos' => $gapsAbiertos,
            'acciones_pendientes' => $accionesPendientes,
            'evidencias' => $evidencias,
            'porcentaje_cumplimiento' => $porcentajeCumplimiento
        ];
    }

    /**
     * Genera CSV de datos
     */
    public function generarCSV(array $datos, array $cabeceras): string
    {
        $output = fopen('php://temp', 'r+');
        
        // BOM para UTF-8 en Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeceras
        fputcsv($output, $cabeceras, ';');
        
        // Datos
        foreach ($datos as $fila) {
            $row = [];
            foreach ($cabeceras as $key => $label) {
                $row[] = $fila[$key] ?? '';
            }
            fputcsv($output, $row, ';');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
     * Genera HTML para PDF del SOA
     */
    public function generarHTMLSOA(array $empresa): string
    {
        $soa = $this->getSOACompleto();
        $resumen = $this->getResumenCumplimiento();
        
        $html = $this->getHTMLHeader($empresa['nombre'] ?? 'Empresa', 'Declaración de Aplicabilidad (SOA)');
        
        // Resumen ejecutivo
        $html .= '<div class="section">
            <h2>Resumen Ejecutivo</h2>
            <table class="info-table">
                <tr><td><strong>Fecha de Generación:</strong></td><td>' . $resumen['fecha_generacion'] . '</td></tr>
                <tr><td><strong>Total Controles ISO 27001:</strong></td><td>' . $resumen['total_controles'] . '</td></tr>
                <tr><td><strong>Controles Aplicables:</strong></td><td>' . $resumen['controles_aplicables'] . '</td></tr>
                <tr><td><strong>Porcentaje de Cumplimiento:</strong></td><td><strong>' . $resumen['porcentaje_cumplimiento'] . '%</strong></td></tr>
            </table>
        </div>';
        
        // Estado de implementación
        $html .= '<div class="section">
            <h2>Estado de Implementación</h2>
            <table class="info-table">
                <tr><td>Implementado:</td><td>' . ($resumen['estados_implementacion']['implementado'] ?? 0) . '</td></tr>
                <tr><td>Parcialmente Implementado:</td><td>' . ($resumen['estados_implementacion']['parcial'] ?? 0) . '</td></tr>
                <tr><td>No Implementado:</td><td>' . ($resumen['estados_implementacion']['no_implementado'] ?? 0) . '</td></tr>
                <tr><td>No Aplica:</td><td>' . ($resumen['estados_implementacion']['no_aplica'] ?? 0) . '</td></tr>
            </table>
        </div>';
        
        // Tabla SOA
        $html .= '<div class="section">
            <h2>Detalle de Controles</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Control</th>
                        <th>Aplica</th>
                        <th>Estado</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($soa as $control) {
            $aplicaClass = $control['aplicabilidad'] == 'aplica' ? '' : 'style="color: #999;"';
            $estadoClass = $this->getEstadoClass($control['estado_implementacion']);
            
            $html .= '<tr ' . $aplicaClass . '>
                <td>' . htmlspecialchars($control['codigo']) . '</td>
                <td>' . htmlspecialchars($control['control_nombre']) . '</td>
                <td>' . htmlspecialchars($control['aplicabilidad'] ?? 'Sin definir') . '</td>
                <td class="' . $estadoClass . '">' . htmlspecialchars($control['estado_implementacion'] ?? '-') . '</td>
                <td>' . htmlspecialchars($control['responsable'] ?? '-') . '</td>
            </tr>';
        }
        
        $html .= '</tbody></table></div>';
        $html .= $this->getHTMLFooter();
        
        return $html;
    }

    /**
     * Genera HTML para PDF de GAPs
     */
    public function generarHTMLGAPs(array $empresa): string
    {
        $gaps = $this->getGAPsYAcciones();
        
        $html = $this->getHTMLHeader($empresa['nombre'] ?? 'Empresa', 'Reporte de GAPs y Acciones');
        
        $html .= '<div class="section">
            <h2>Listado de GAPs y Acciones Correctivas</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Control</th>
                        <th>Brecha</th>
                        <th>Prioridad</th>
                        <th>Estado GAP</th>
                        <th>Acción</th>
                        <th>Responsable</th>
                        <th>Fecha Límite</th>
                        <th>Avance</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($gaps as $gap) {
            $prioridadClass = $this->getPrioridadClass($gap['prioridad']);
            
            $html .= '<tr>
                <td>' . htmlspecialchars($gap['control_codigo']) . '</td>
                <td>' . htmlspecialchars(substr($gap['brecha'], 0, 100)) . '</td>
                <td class="' . $prioridadClass . '">' . htmlspecialchars($gap['prioridad']) . '</td>
                <td>' . htmlspecialchars($gap['gap_estado']) . '</td>
                <td>' . htmlspecialchars(substr($gap['accion_descripcion'] ?? '-', 0, 80)) . '</td>
                <td>' . htmlspecialchars($gap['responsable'] ?? '-') . '</td>
                <td>' . htmlspecialchars($gap['fecha_compromiso'] ?? '-') . '</td>
                <td>' . ($gap['avance'] ?? 0) . '%</td>
            </tr>';
        }
        
        $html .= '</tbody></table></div>';
        $html .= $this->getHTMLFooter();
        
        return $html;
    }

    /**
     * Genera HTML para PDF de Reporte Ejecutivo
     */
    public function generarHTMLEjecutivo(array $empresa): string
    {
        $resumen = $this->getResumenCumplimiento();
        
        $html = $this->getHTMLHeader($empresa['nombre'] ?? 'Empresa', 'Reporte Ejecutivo de Cumplimiento ISO 27001');
        
        // KPIs principales
        $html .= '<div class="section">
            <h2>Indicadores Clave</h2>
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-value">' . $resumen['porcentaje_cumplimiento'] . '%</div>
                    <div class="kpi-label">Cumplimiento General</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-value">' . $resumen['controles_aplicables'] . '/' . $resumen['total_controles'] . '</div>
                    <div class="kpi-label">Controles Aplicables</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-value">' . $resumen['gaps_abiertos'] . '</div>
                    <div class="kpi-label">GAPs Abiertos</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-value">' . $resumen['acciones_pendientes'] . '</div>
                    <div class="kpi-label">Acciones Pendientes</div>
                </div>
            </div>
        </div>';
        
        // Estado de implementación
        $html .= '<div class="section">
            <h2>Estado de Implementación de Controles</h2>
            <table class="info-table">
                <tr>
                    <td style="background: #28a745; color: white;">Implementado</td>
                    <td>' . ($resumen['estados_implementacion']['implementado'] ?? 0) . '</td>
                </tr>
                <tr>
                    <td style="background: #ffc107;">Parcialmente Implementado</td>
                    <td>' . ($resumen['estados_implementacion']['parcial'] ?? 0) . '</td>
                </tr>
                <tr>
                    <td style="background: #dc3545; color: white;">No Implementado</td>
                    <td>' . ($resumen['estados_implementacion']['no_implementado'] ?? 0) . '</td>
                </tr>
            </table>
        </div>';
        
        // Estado de evidencias
        $html .= '<div class="section">
            <h2>Estado de Evidencias</h2>
            <table class="info-table">
                <tr>
                    <td style="background: #28a745; color: white;">Validadas</td>
                    <td>' . ($resumen['evidencias']['validada'] ?? 0) . '</td>
                </tr>
                <tr>
                    <td style="background: #ffc107;">Pendientes de Validación</td>
                    <td>' . ($resumen['evidencias']['pendiente'] ?? 0) . '</td>
                </tr>
                <tr>
                    <td style="background: #dc3545; color: white;">Rechazadas</td>
                    <td>' . ($resumen['evidencias']['rechazada'] ?? 0) . '</td>
                </tr>
            </table>
        </div>';
        
        $html .= $this->getHTMLFooter();
        
        return $html;
    }

    private function getHTMLHeader(string $empresaNombre, string $titulo): string
    {
        return '<!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($titulo) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
                .header { background: #2c3e50; color: white; padding: 20px; margin-bottom: 20px; }
                .header h1 { margin: 0 0 5px 0; font-size: 24px; }
                .header p { margin: 0; opacity: 0.8; }
                .section { margin-bottom: 30px; }
                .section h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
                .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
                .data-table th { background: #3498db; color: white; padding: 8px; text-align: left; }
                .data-table td { padding: 6px 8px; border-bottom: 1px solid #ddd; }
                .data-table tr:nth-child(even) { background: #f9f9f9; }
                .info-table { width: 50%; border-collapse: collapse; }
                .info-table td { padding: 8px; border: 1px solid #ddd; }
                .info-table td:first-child { background: #f8f9fa; font-weight: bold; width: 40%; }
                .kpi-grid { display: flex; gap: 20px; flex-wrap: wrap; }
                .kpi-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; min-width: 150px; border-left: 4px solid #3498db; }
                .kpi-value { font-size: 28px; font-weight: bold; color: #2c3e50; }
                .kpi-label { color: #666; margin-top: 5px; }
                .estado-implementado { color: #28a745; font-weight: bold; }
                .estado-parcial { color: #ffc107; font-weight: bold; }
                .estado-no_implementado { color: #dc3545; font-weight: bold; }
                .prioridad-alta { color: #dc3545; font-weight: bold; }
                .prioridad-media { color: #ffc107; font-weight: bold; }
                .prioridad-baja { color: #28a745; }
                .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 10px; color: #666; text-align: center; }
                @media print { body { margin: 0; } .header { -webkit-print-color-adjust: exact; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . htmlspecialchars($empresaNombre) . '</h1>
                <p>' . htmlspecialchars($titulo) . ' - Generado: ' . date('d/m/Y H:i') . '</p>
            </div>';
    }

    private function getHTMLFooter(): string
    {
        return '<div class="footer">
                <p>ISO 27001 Compliance Platform - Documento generado automaticamente</p>
                <p>Este documento es confidencial y para uso interno unicamente.</p>
            </div>
        </body>
        </html>';
    }

    private function getEstadoClass(?string $estado = null): string
    {
        return match($estado) {
            'implementado' => 'estado-implementado',
            'parcial' => 'estado-parcial',
            'no_implementado' => 'estado-no_implementado',
            default => ''
        };
    }

    private function getPrioridadClass(?string $prioridad = null): string
    {
        return match(strtolower($prioridad ?? '')) {
            'alta' => 'prioridad-alta',
            'media' => 'prioridad-media',
            'baja' => 'prioridad-baja',
            default => ''
        };
    }
}
