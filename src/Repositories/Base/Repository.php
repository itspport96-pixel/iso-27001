<?php

namespace App\Repositories\Base;

use App\Core\Database;
use App\Core\TenantContext;
use PDO;

abstract class Repository
{
    protected PDO $db;
    protected string $table;
    protected bool $usesTenant = true;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollBack();
    }

    protected function applyScopeWhere(string &$sql, array &$params): void
    {
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
    }

    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];
        
        $this->applyScopeWhere($sql, $params);
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function exists(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE id = :id";
        $params = [':id' => $id];
        
        $this->applyScopeWhere($sql, $params);
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return (int)$stmt->fetchColumn() > 0;
    }

    public function findByColumn(string $column, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        $params = [':value' => $value];
        
        $this->applyScopeWhere($sql, $params);
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function findAllByColumn(string $column, $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value";
        $params = [':value' => $value];
        
        $this->applyScopeWhere($sql, $params);
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByConditions(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE 1=1";
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $sql .= " AND {$field} = :{$field}";
            $params[":{$field}"] = $value;
        }
        
        $this->applyScopeWhere($sql, $params);
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return (int)$stmt->fetchColumn();
    }

    public function paginate(int $page = 1, int $perPage = 20, array $conditions = [], string $orderBy = 'id', string $order = 'DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        foreach ($conditions as $field => $value) {
            $sql .= " AND {$field} = :{$field}";
            $params[":{$field}"] = $value;
        }
        
        $this->applyScopeWhere($sql, $params);
        
        $sql .= " ORDER BY {$orderBy} {$order} LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->countByConditions($conditions);
        
        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => (int)ceil($total / $perPage)
        ];
    }
}
