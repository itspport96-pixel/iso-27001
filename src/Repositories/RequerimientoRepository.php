<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class RequerimientoRepository extends Repository
{
    protected string $table = 'empresa_requerimientos';
    protected bool $usesTenant = true;

    public function getWithRequerimientoBase(): array
    {
        $sql = "SELECT er.*, rb.numero, rb.identificador, rb.titulo, rb.descripcion
                FROM {$this->table} er
                INNER JOIN requerimientos_base rb ON er.requerimiento_id = rb.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE er.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY rb.numero ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT er.*, rb.numero, rb.identificador, rb.titulo, rb.descripcion
                FROM {$this->table} er
                INNER JOIN requerimientos_base rb ON er.requerimiento_id = rb.id
                WHERE er.id = :id";
        
        $params = [':id' => $id];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND er.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getControlesAsociados(int $requerimientoId): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        $sql = "SELECT c.id, c.codigo, c.nombre, c.descripcion,
                d.nombre as dominio_nombre,
                s.id as soa_id,
                s.estado as estado_implementacion,
                s.aplicable
                FROM requerimientos_controles rc
                INNER JOIN controles c ON rc.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN soa_entries s ON c.id = s.control_id AND s.empresa_id = :empresa_id
                WHERE rc.requerimiento_base_id = :requerimiento_id
                ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':requerimiento_id', $requerimientoId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticas(): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados
                FROM {$this->table}";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['total'] > 0) {
            $result['porcentaje'] = round(($result['completados'] / $result['total']) * 100, 2);
        } else {
            $result['porcentaje'] = 0;
        }
        
        return $result;
    }

    public function calcularProgresoRequerimiento(int $requerimientoId): array
    {
        $controles = $this->getControlesAsociados($requerimientoId);
        
        $total = count($controles);
        $aplicables = 0;
        $implementados = 0;
        $controlesConEvidencias = 0;
        
        foreach ($controles as $control) {
            if ($control['aplicable'] == 1) {
                $aplicables++;
                
                if ($control['estado_implementacion'] === 'implementado') {
                    $implementados++;
                    
                    // Verificar si tiene evidencias aprobadas
                    if ($this->controlTieneEvidenciasAprobadas($control['id'])) {
                        $controlesConEvidencias++;
                    }
                }
            }
        }
        
        $porcentaje = $aplicables > 0 ? round(($controlesConEvidencias / $aplicables) * 100, 2) : 0;
        
        return [
            'total_controles' => $total,
            'controles_aplicables' => $aplicables,
            'controles_implementados' => $implementados,
            'controles_con_evidencias' => $controlesConEvidencias,
            'porcentaje' => $porcentaje,
            'cumple_completitud' => ($aplicables > 0 && $controlesConEvidencias === $aplicables)
        ];
    }

    private function controlTieneEvidenciasAprobadas(int $controlId): bool
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        $sql = "SELECT COUNT(*) 
                FROM evidencias 
                WHERE control_id = :control_id 
                AND empresa_id = :empresa_id 
                AND estado_validacion = 'aprobada'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':control_id', $controlId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return (int)$stmt->fetchColumn() > 0;
    }

    public function verificarYActualizarCompletitud(int $requerimientoId): bool
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        // Obtener el registro de empresa_requerimientos
        $sqlReq = "SELECT id, estado FROM {$this->table} 
                   WHERE requerimiento_id = :requerimiento_id 
                   AND empresa_id = :empresa_id 
                   LIMIT 1";
        
        $stmtReq = $this->db->prepare($sqlReq);
        $stmtReq->bindValue(':requerimiento_id', $requerimientoId, PDO::PARAM_INT);
        $stmtReq->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmtReq->execute();
        
        $empresaReq = $stmtReq->fetch(PDO::FETCH_ASSOC);
        
        if (!$empresaReq) {
            return false;
        }
        
        // Calcular progreso
        $progreso = $this->calcularProgresoRequerimiento($requerimientoId);
        
        $nuevoEstado = null;
        $fechaCompletado = null;
        
        if ($progreso['cumple_completitud']) {
            // Todos los controles implementados + evidencias aprobadas
            $nuevoEstado = 'completado';
            $fechaCompletado = date('Y-m-d');
        } elseif ($progreso['controles_implementados'] > 0 || $progreso['controles_con_evidencias'] > 0) {
            // Hay al menos algún control en progreso
            if ($empresaReq['estado'] !== 'completado') {
                $nuevoEstado = 'en_proceso';
            }
        } else {
            // Ningún control implementado
            if ($empresaReq['estado'] !== 'completado') {
                $nuevoEstado = 'pendiente';
            }
        }
        
        // Solo actualizar si cambió el estado
        if ($nuevoEstado && $nuevoEstado !== $empresaReq['estado']) {
            $sqlUpdate = "UPDATE {$this->table} 
                          SET estado = :estado,
                              fecha_completado = :fecha_completado,
                              updated_at = NOW()
                          WHERE id = :id 
                          AND empresa_id = :empresa_id";
            
            $stmtUpdate = $this->db->prepare($sqlUpdate);
            $stmtUpdate->bindValue(':estado', $nuevoEstado);
            $stmtUpdate->bindValue(':fecha_completado', $fechaCompletado);
            $stmtUpdate->bindValue(':id', $empresaReq['id'], PDO::PARAM_INT);
            $stmtUpdate->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
            
            return $stmtUpdate->execute();
        }
        
        return false;
    }

    public function verificarTodosLosRequerimientos(): void
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        $sql = "SELECT DISTINCT requerimiento_id 
                FROM {$this->table} 
                WHERE empresa_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        $requerimientos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($requerimientos as $reqId) {
            $this->verificarYActualizarCompletitud($reqId);
        }
    }
}
