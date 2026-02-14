<?php

namespace App\Models;

use App\Models\Base\Model;

class SOA extends Model
{
    protected string $table = 'soa_entries';
    protected bool $usesTenant = true;
    protected bool $usesTimestamps = true;
    protected bool $usesSoftDelete = false;

    public function findByControl(int $controlId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE control_id = :control_id";
        $params = [':control_id' => $controlId];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function getWithControl(): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, 
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " WHERE s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY d.codigo ASC, c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByEstado(string $estado): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                WHERE s.estado = :estado";
        
        $params = [':estado' => $estado];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countByEstado(): array
    {
        $sql = "SELECT estado, COUNT(*) as total 
                FROM {$this->table} 
                WHERE aplicable = 1";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " GROUP BY estado";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAplicables(): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                WHERE s.aplicable = 1";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function calculateProgress(): array
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
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " WHERE empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result && $result['aplicables'] > 0) {
            $result['porcentaje'] = round(($result['implementados'] / $result['aplicables']) * 100, 2);
        } else {
            $result['porcentaje'] = 0;
        }
        
        return $result;
    }
}
