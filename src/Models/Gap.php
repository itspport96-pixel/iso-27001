<?php

namespace App\Models;

use App\Models\Base\Model;

class Gap extends Model
{
    protected string $table = 'gap_items';
    protected bool $usesTenant = true;
    protected bool $usesTimestamps = true;
    protected bool $usesSoftDelete = false;

    public function getBySoaId(int $soaId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE soa_id = :soa_id AND estado_gap = 'activo'";
        $params = [':soa_id' => $soaId];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY prioridad DESC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByPrioridad(string $prioridad): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE prioridad = :prioridad AND estado_gap = 'activo'";
        $params = [':prioridad' => $prioridad];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET estado_gap = 'eliminado' WHERE id = :id";
        $params = [':id' => $id];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
}
