<?php

namespace App\Models\Base;

use App\Core\Database;
use App\Core\TenantContext;
use PDO;

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';
    protected bool $usesTenant = true;
    protected bool $usesTimestamps = true;
    protected bool $usesSoftDelete = false;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        if ($this->usesSoftDelete) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($this->usesTenant) {
            $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function findAll(array $conditions = [], int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        if ($this->usesSoftDelete) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        foreach ($conditions as $field => $value) {
            $sql .= " AND {$field} = :{$field}";
            $params[":{$field}"] = $value;
        }
        
        $sql .= " LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): ?int
    {
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $data['empresa_id'] = $tenantId;
        }
        
        if ($this->usesTimestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ":{$field}", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        
        return $stmt->execute() ? (int)$this->db->lastInsertId() : null;
    }

    public function update(int $id, array $data): bool
    {
        if ($this->usesTimestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "{$field} = :{$field}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . 
               " WHERE {$this->primaryKey} = :id";
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        if ($this->usesSoftDelete) {
            $sql .= " AND deleted_at IS NULL";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($this->usesTenant) {
            $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        }
        
        foreach ($data as $field => $value) {
            $stmt->bindValue(":{$field}", $value);
        }
        
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        if ($this->usesSoftDelete) {
            return $this->update($id, ['deleted_at' => date('Y-m-d H:i:s')]);
        }
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        if ($this->usesTenant) {
            $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        }
        
        return $stmt->execute();
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        if ($this->usesSoftDelete) {
            $sql .= " AND deleted_at IS NULL";
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
        return (int)$stmt->fetchColumn();
    }
}
