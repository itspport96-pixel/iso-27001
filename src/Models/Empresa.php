<?php

namespace App\Models;

use App\Models\Base\Model;
use App\Models\Base\SoftDelete;

class Empresa extends Model
{
    use SoftDelete;

    protected string $table = 'empresas';
    protected bool $usesTenant = false;
    protected bool $usesTimestamps = true;
    protected bool $usesSoftDelete = true;

    public function findByRuc(string $ruc): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ruc = :ruc AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ruc', $ruc);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function rucExists(string $ruc, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE ruc = :ruc AND deleted_at IS NULL";
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':ruc', $ruc);
        
        if ($excludeId) {
            $stmt->bindValue(':exclude_id', $excludeId, \PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getWithUsersCount(): array
    {
        $sql = "SELECT e.*, COUNT(u.id) as total_usuarios 
                FROM {$this->table} e 
                LEFT JOIN usuarios u ON e.id = u.empresa_id AND u.deleted_at IS NULL
                WHERE e.deleted_at IS NULL
                GROUP BY e.id
                ORDER BY e.created_at DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
