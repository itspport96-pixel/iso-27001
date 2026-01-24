<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class EvidenciaRepository extends Repository
{
    protected string $table = 'evidencias';
    protected bool $usesTenant = true;

    public function getWithControlInfo(): array
    {
        $sql = "SELECT e.*, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                u1.nombre as subido_por_nombre,
                u2.nombre as validado_por_nombre
                FROM {$this->table} e
                INNER JOIN controles c ON e.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                LEFT JOIN usuarios u2 ON e.validado_por = u2.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE e.empresa_id = :empresa_id";
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

    public function getByControlId(int $controlId): array
    {
        $sql = "SELECT e.*, u1.nombre as subido_por_nombre, u2.nombre as validado_por_nombre
                FROM {$this->table} e
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                LEFT JOIN usuarios u2 ON e.validado_por = u2.id
                WHERE e.control_id = :control_id";
        
        $params = [':control_id' => $controlId];
        
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

    public function getByEstadoValidacion(string $estado): array
    {
        $sql = "SELECT e.*, c.codigo, c.nombre as control_nombre,
                u1.nombre as subido_por_nombre
                FROM {$this->table} e
                INNER JOIN controles c ON e.control_id = c.id
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                WHERE e.estado_validacion = :estado";
        
        $params = [':estado' => $estado];
        
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

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT e.*, c.codigo, c.nombre as control_nombre, c.descripcion,
                d.nombre as dominio_nombre,
                u1.nombre as subido_por_nombre, u1.email as subido_por_email,
                u2.nombre as validado_por_nombre, u2.email as validado_por_email
                FROM {$this->table} e
                INNER JOIN controles c ON e.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                LEFT JOIN usuarios u2 ON e.validado_por = u2.id
                WHERE e.id = :id";
        
        $params = [':id' => $id];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND e.empresa_id = :empresa_id";
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

    public function getControlesConEvidencias(): array
    {
        $sql = "SELECT c.id as control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                COUNT(e.id) as total_evidencias,
                SUM(CASE WHEN e.estado_validacion = 'aprobada' THEN 1 ELSE 0 END) as evidencias_aprobadas,
                SUM(CASE WHEN e.estado_validacion = 'pendiente' THEN 1 ELSE 0 END) as evidencias_pendientes
                FROM controles c
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN evidencias e ON c.id = e.control_id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND e.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " GROUP BY c.id ORDER BY d.codigo ASC, c.codigo ASC";
        
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
                SUM(CASE WHEN estado_validacion = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado_validacion = 'aprobada' THEN 1 ELSE 0 END) as aprobadas,
                SUM(CASE WHEN estado_validacion = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                SUM(tamano) as tamano_total
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
        
        if ($result) {
            $result['tamano_total_mb'] = round($result['tamano_total'] / 1048576, 2);
        }
        
        return $result;
    }
}
