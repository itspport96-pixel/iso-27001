<?php

namespace App\Models;

use App\Models\Base\Model;

class Accion extends Model
{
    protected string $table = 'acciones';
    protected bool $usesTenant = false;
    protected bool $usesTimestamps = true;
    protected bool $usesSoftDelete = false;

    public function getByGapId(int $gapId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE gap_id = :gap_id 
                AND estado_accion = 'activo' 
                ORDER BY fecha_compromiso ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':gap_id', $gapId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getByEstado(string $estado): array
    {
        $sql = "SELECT a.*, g.brecha, g.empresa_id 
                FROM {$this->table} a
                INNER JOIN gap_items g ON a.gap_id = g.id
                WHERE a.estado = :estado 
                AND a.estado_accion = 'activo'
                AND g.estado_gap = 'activo'";
        
        $params = [':estado' => $estado];
        
        $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
        if ($tenantId) {
            $sql .= " AND g.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY a.fecha_compromiso ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getVencidas(): array
    {
        $sql = "SELECT a.*, g.brecha, g.empresa_id 
                FROM {$this->table} a
                INNER JOIN gap_items g ON a.gap_id = g.id
                WHERE a.fecha_compromiso < CURDATE() 
                AND a.estado != 'completada'
                AND a.estado_accion = 'activo'
                AND g.estado_gap = 'activo'";
        
        $params = [];
        
        $tenantId = \App\Core\TenantContext::getInstance()->getTenant();
        if ($tenantId) {
            $sql .= " AND g.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY a.fecha_compromiso ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function completar(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET estado = 'completada', 
                    fecha_completado = CURDATE(),
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET estado_accion = 'eliminado' 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        return $stmt->execute();
    }
}
