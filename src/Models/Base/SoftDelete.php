<?php

namespace App\Models\Base;

trait SoftDelete
{
    public function restore(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NULL WHERE {$this->primaryKey} = :id";
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        if ($this->usesTenant) {
            $stmt->bindValue(':empresa_id', $tenantId, \PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }

    public function forceDelete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        if ($this->usesTenant) {
            $stmt->bindValue(':empresa_id', $tenantId, \PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }

    public function findWithTrashed(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        if ($this->usesTenant) {
            $stmt->bindValue(':empresa_id', $tenantId, \PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function onlyTrashed(array $conditions = []): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NOT NULL";
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        foreach ($conditions as $field => $value) {
            $sql .= " AND {$field} = :{$field}";
            $params[":{$field}"] = $value;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
