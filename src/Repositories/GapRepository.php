<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class GapRepository extends Repository
{
    protected string $table = 'gap_items';
    protected bool $usesTenant = true;

    public function getWithControlInfo(): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }
        
        $sql = "SELECT g.*, s.control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo') as total_acciones,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo' AND estado = 'completada') as acciones_completadas
                FROM {$this->table} g
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE g.estado_gap = 'activo'
                AND g.empresa_id = :empresa_id1
                AND s.empresa_id = :empresa_id2
                ORDER BY g.prioridad DESC, g.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id1', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id2', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByPrioridad(string $prioridad): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }
        
        $sql = "SELECT g.*, s.control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo') as total_acciones,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo' AND estado = 'completada') as acciones_completadas
                FROM {$this->table} g
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE g.prioridad = :prioridad
                AND g.estado_gap = 'activo'
                AND g.empresa_id = :empresa_id1
                AND s.empresa_id = :empresa_id2
                ORDER BY g.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':prioridad', $prioridad);
        $stmt->bindValue(':empresa_id1', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id2', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWithDetails(int $id): ?array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }
        
        $sql = "SELECT g.*, s.control_id, s.estado as control_estado, s.aplicable,
                c.codigo, c.nombre as control_nombre, c.descripcion, c.objetivo,
                d.nombre as dominio_nombre
                FROM {$this->table} g
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE g.id = :id
                AND g.estado_gap = 'activo'
                AND g.empresa_id = :empresa_id1
                AND s.empresa_id = :empresa_id2
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id1', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id2', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getControlesAplicablesNoImplementados(): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }
        
        $sql = "SELECT s.id as soa_id, s.control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre
                FROM soa_entries s
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE s.aplicable = 1
                AND s.estado IN ('no_implementado', 'parcial')
                AND s.empresa_id = :empresa_id1
                AND NOT EXISTS (
                    SELECT 1 FROM gap_items g 
                    WHERE g.soa_id = s.id 
                    AND g.estado_gap = 'activo'
                    AND g.empresa_id = :empresa_id2
                )
                ORDER BY d.codigo ASC, c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id1', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id2', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticas(): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN prioridad = 'alta' THEN 1 ELSE 0 END) as prioridad_alta,
                SUM(CASE WHEN prioridad = 'media' THEN 1 ELSE 0 END) as prioridad_media,
                SUM(CASE WHEN prioridad = 'baja' THEN 1 ELSE 0 END) as prioridad_baja,
                SUM(CASE WHEN avance = 100 THEN 1 ELSE 0 END) as completados,
                SUM(CASE WHEN avance < 100 AND fecha_objetivo < CURDATE() THEN 1 ELSE 0 END) as vencidos,
                AVG(avance) as avance_promedio
                FROM {$this->table}
                WHERE estado_gap = 'activo'
                AND empresa_id = :empresa_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['avance_promedio'] = $result['avance_promedio'] !== null 
                ? round((float)$result['avance_promedio'], 2) 
                : 0;
            $result['pendientes'] = $result['total'] - $result['completados'];
        }
        
        return $result;
    }

    public function validarSoaPermiteGap(int $soaId): bool
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }
        
        $sql = "SELECT aplicable, estado FROM soa_entries 
                WHERE id = :soa_id 
                AND empresa_id = :empresa_id
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':soa_id', $soaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        $soa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$soa) {
            return false;
        }
        
        if ($soa['aplicable'] != 1 || !in_array($soa['estado'], ['no_implementado', 'parcial'])) {
            return false;
        }
        
        $sqlGap = "SELECT COUNT(*) FROM {$this->table} 
                   WHERE soa_id = :soa_id 
                   AND estado_gap = 'activo'
                   AND empresa_id = :empresa_id";
        
        $stmtGap = $this->db->prepare($sqlGap);
        $stmtGap->bindValue(':soa_id', $soaId, PDO::PARAM_INT);
        $stmtGap->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmtGap->execute();
        
        return (int)$stmtGap->fetchColumn() === 0;
    }
}
