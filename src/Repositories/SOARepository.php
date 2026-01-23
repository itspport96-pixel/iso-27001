<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class SOARepository extends Repository
{
    protected string $table = 'soa_entries';
    protected bool $usesTenant = true;

    public function getWithControlInfo(): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, c.descripcion,
                d.codigo as dominio_codigo, d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE s.empresa_id = :empresa_id";
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

    public function getByFiltros($dominioId = null, $estado = null, $aplicable = null): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, 
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        if ($dominioId) {
            $sql .= " AND c.dominio_id = :dominio_id";
            $params[':dominio_id'] = $dominioId;
        }
        
        if ($estado) {
            $sql .= " AND s.estado = :estado";
            $params[':estado'] = $estado;
        }
        
        if ($aplicable !== null && $aplicable !== '') {
            $sql .= " AND s.aplicable = :aplicable";
            $params[':aplicable'] = (int)$aplicable;
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEstado(string $estado): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, 
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE s.estado = :estado";
        
        $params = [':estado' => $estado];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDominio(int $dominioId): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, 
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE c.dominio_id = :dominio_id";
        
        $params = [':dominio_id' => $dominioId];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByControlId(int $controlId): ?array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, c.descripcion, c.objetivo,
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE s.control_id = :control_id";
        
        $params = [':control_id' => $controlId];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
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

    public function updateEstado(int $soaId, string $estado, ?string $justificacion = null): bool
    {
        $sql = "UPDATE {$this->table} 
                SET estado = :estado, 
                    justificacion = :justificacion,
                    fecha_evaluacion = CURDATE(),
                    updated_at = NOW() 
                WHERE id = :id";
        
        $params = [
            ':id' => $soaId,
            ':estado' => $estado,
            ':justificacion' => $justificacion
        ];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }

    public function getEstadisticas(): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN aplicable = 1 THEN 1 ELSE 0 END) as aplicables,
                SUM(CASE WHEN aplicable = 1 AND estado = 'implementado' THEN 1 ELSE 0 END) as implementados,
                SUM(CASE WHEN aplicable = 1 AND estado = 'parcial' THEN 1 ELSE 0 END) as parciales,
                SUM(CASE WHEN aplicable = 1 AND estado = 'no_implementado' THEN 1 ELSE 0 END) as no_implementados
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
        
        if ($result && $result['aplicables'] > 0) {
            $result['porcentaje'] = round(($result['implementados'] / $result['aplicables']) * 100, 2);
        } else {
            $result['porcentaje'] = 0;
        }
        
        return $result;
    }

    public function getEstadisticasPorDominio(): array
    {
        $sql = "SELECT 
                d.codigo as dominio_codigo,
                d.nombre as dominio_nombre,
                COUNT(*) as total,
                SUM(CASE WHEN s.aplicable = 1 THEN 1 ELSE 0 END) as aplicables,
                SUM(CASE WHEN s.aplicable = 1 AND s.estado = 'implementado' THEN 1 ELSE 0 END) as implementados
                FROM {$this->table} s
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " GROUP BY d.id ORDER BY d.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as &$result) {
            if ($result['aplicables'] > 0) {
                $result['porcentaje'] = round(($result['implementados'] / $result['aplicables']) * 100, 2);
            } else {
                $result['porcentaje'] = 0;
            }
        }
        
        return $results;
    }
}
