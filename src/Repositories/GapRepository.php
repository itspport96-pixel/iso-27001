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
        $sql = "SELECT g.*, s.control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo') as total_acciones,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo' AND estado = 'completada') as acciones_completadas
                FROM {$this->table} g
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE g.estado_gap = 'activo'";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND g.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY g.prioridad DESC, g.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByPrioridad(string $prioridad): array
    {
        $sql = "SELECT g.*, s.control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo') as total_acciones,
                (SELECT COUNT(*) FROM acciones WHERE gap_id = g.id AND estado_accion = 'activo' AND estado = 'completada') as acciones_completadas
                FROM {$this->table} g
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE g.prioridad = :prioridad
                AND g.estado_gap = 'activo'";
        
        $params = [':prioridad' => $prioridad];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND g.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY g.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT g.*, s.control_id, s.estado as control_estado, s.aplicable,
                c.codigo, c.nombre as control_nombre, c.descripcion, c.objetivo,
                d.nombre as dominio_nombre
                FROM {$this->table} g
                INNER JOIN soa_entries s ON g.soa_id = s.id
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE g.id = :id
                AND g.estado_gap = 'activo'";
        
        $params = [':id' => $id];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND g.empresa_id = :empresa_id";
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

    public function getControlesAplicablesNoImplementados(): array
    {
        $sql = "SELECT s.id as soa_id, s.control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre
                FROM soa_entries s
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE s.aplicable = 1
                AND s.estado IN ('no_implementado', 'parcial')";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY d.codigo ASC, c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticas(): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN prioridad = 'alta' THEN 1 ELSE 0 END) as prioridad_alta,
                SUM(CASE WHEN prioridad = 'media' THEN 1 ELSE 0 END) as prioridad_media,
                SUM(CASE WHEN prioridad = 'baja' THEN 1 ELSE 0 END) as prioridad_baja,
                SUM(CASE WHEN avance = 100 THEN 1 ELSE 0 END) as completados,
                SUM(CASE WHEN avance < 100 AND fecha_objetivo < CURDATE() THEN 1 ELSE 0 END) as vencidos,
                AVG(avance) as avance_promedio
                FROM {$this->table}
                WHERE estado_gap = 'activo'";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $result['avance_promedio'] = $result['avance_promedio'] !== null 
                ? round((float)$result['avance_promedio'], 2) 
                : 0;
        }
        
        return $result;
    }

    public function validarSoaPermiteGap(int $soaId): bool
    {
        $sql = "SELECT aplicable, estado FROM soa_entries WHERE id = :soa_id";
        $params = [':soa_id' => $soaId];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $soa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$soa) {
            return false;
        }
        
        return $soa['aplicable'] == 1 && in_array($soa['estado'], ['no_implementado', 'parcial']);
    }

    public function getEvidenciasDelControl(int $gapId): array
    {
        $sql = "SELECT e.id, e.nombre_archivo, e.estado_validacion, e.created_at, e.validado_por,
                u.nombre as validado_por_nombre
                FROM evidencias e
                LEFT JOIN usuarios u ON e.validado_por = u.id
                WHERE e.control_id = (
                    SELECT s.control_id 
                    FROM gap_items g
                    INNER JOIN soa_entries s ON g.soa_id = s.id
                    WHERE g.id = :gap_id
                )";
        
        $params = [':gap_id' => $gapId];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND e.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function tieneEvidenciaAprobada(int $gapId): bool
    {
        $sql = "SELECT COUNT(*) as total
                FROM evidencias e
                WHERE e.control_id = (
                    SELECT s.control_id 
                    FROM gap_items g
                    INNER JOIN soa_entries s ON g.soa_id = s.id
                    WHERE g.id = :gap_id
                )
                AND e.estado_validacion = 'aprobada'";
        
        $params = [':gap_id' => $gapId];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND e.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result && $result['total'] > 0;
    }
}
