<?php

namespace App\Models;

use App\Models\Base\Model;
use App\Models\Base\SoftDelete;

class Usuario extends Model
{
    use SoftDelete;

    protected string $table = 'usuarios';
    protected bool $usesTenant = true;
    protected bool $usesTimestamps = true;
    protected bool $usesSoftDelete = true;

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " AND deleted_at IS NULL LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result ?: null;
    }

    public function findByEmailGlobal(string $email, int $empresaId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE email = :email 
                AND empresa_id = :empresa_id 
                AND deleted_at IS NULL 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':empresa_id', $empresaId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $params = [':email' => $email];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " AND deleted_at IS NULL";
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return (int)$stmt->fetchColumn() > 0;
    }

    public function incrementLoginAttempts(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET intentos_login = intentos_login + 1 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function resetLoginAttempts(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET intentos_login = 0, bloqueado_hasta = NULL 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function blockUser(int $id, int $minutes = 15): bool
    {
        $bloqueadoHasta = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));
        
        $sql = "UPDATE {$this->table} 
                SET bloqueado_hasta = :bloqueado_hasta 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        $stmt->bindValue(':bloqueado_hasta', $bloqueadoHasta);
        
        return $stmt->execute();
    }

    public function updateLastAccess(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET ultimo_acceso = NOW() 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function getByRole(string $rol): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE rol = :rol";
        $params = [':rol' => $rol];
        
        if ($this->usesTenant) {
            $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " AND deleted_at IS NULL ORDER BY nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
