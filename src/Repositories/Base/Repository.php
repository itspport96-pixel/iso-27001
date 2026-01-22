<?php
declare(strict_types=1);

namespace App\Repositories\Base;

use App\Core\Database;
use PDO;

abstract class Repository
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function find(int $id, int $empresaId): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id AND empresa_id = :empresa_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function findAll(int $empresaId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE empresa_id = :empresa_id ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $cols = implode(', ', array_keys($data));
        $place = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$cols}) VALUES ({$place})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $empresaId, array $data): int
    {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = :$col";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = :id AND empresa_id = :empresa_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($data, ['id' => $id, 'empresa_id' => $empresaId]));
        return $stmt->rowCount();
    }

    public function delete(int $id, int $empresaId): int
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id AND empresa_id = :empresa_id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'empresa_id' => $empresaId]);
        return $stmt->rowCount();
    }

    public function softDelete(int $id, int $empresaId, string $field = 'estado'): int
    {
        return $this->update($id, $empresaId, [$field => 'eliminado']);
    }
}
