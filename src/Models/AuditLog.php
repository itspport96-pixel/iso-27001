<?php

namespace App\Models;

use App\Models\Base\Model;

class AuditLog extends Model
{
    protected string $table = 'audit_logs';
    protected bool $usesTenant = true;
    protected bool $usesTimestamps = false;
    protected bool $usesSoftDelete = false;

    public function getByUsuario(int $usuarioId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE usuario_id = :usuario_id";
        $params = [':usuario_id' => $usuarioId];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByTabla(string $tabla): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE tabla = :tabla";
        $params = [':tabla' => $tabla];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 100";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
