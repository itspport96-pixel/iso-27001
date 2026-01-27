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
        // Obtener gap_id antes de actualizar
        $accion = $this->find($id);
        if (!$accion) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} 
                SET estado = 'completada', 
                    fecha_completado = CURDATE(),
                    updated_at = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        // Recalcular avance del GAP
        if ($result && isset($accion['gap_id'])) {
            $this->recalcularAvanceGap($accion['gap_id']);
        }
        
        return $result;
    }

    public function update(int $id, array $data): bool
    {
        // Obtener gap_id antes de actualizar
        $accion = $this->find($id);
        if (!$accion) {
            return false;
        }
        
        // Llamar al update del padre
        $result = parent::update($id, $data);
        
        // Recalcular avance del GAP si se actualizó el estado
        if ($result && isset($accion['gap_id']) && isset($data['estado'])) {
            $this->recalcularAvanceGap($accion['gap_id']);
        }
        
        return $result;
    }

    public function create(array $data): int
    {
        // Llamar al create del padre
        $id = parent::create($data);
        
        // Recalcular avance del GAP
        if ($id && isset($data['gap_id'])) {
            $this->recalcularAvanceGap($data['gap_id']);
        }
        
        return $id;
    }

    public function softDelete(int $id): bool
    {
        // Obtener gap_id antes de eliminar
        $accion = $this->find($id);
        if (!$accion) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} 
                SET estado_accion = 'eliminado' 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        // Recalcular avance del GAP
        if ($result && isset($accion['gap_id'])) {
            $this->recalcularAvanceGap($accion['gap_id']);
        }
        
        return $result;
    }

    /**
     * Recalcula el avance de un GAP basado en sus acciones completadas
     */
    private function recalcularAvanceGap(int $gapId): void
    {
        $sql = "SELECT 
                COUNT(*) as total_acciones,
                SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as acciones_completadas
                FROM {$this->table}
                WHERE gap_id = :gap_id 
                AND estado_accion = 'activo'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':gap_id', $gapId, \PDO::PARAM_INT);
        $stmt->execute();
        
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        $avance = 0;
        if ($stats && $stats['total_acciones'] > 0) {
            $avance = round(($stats['acciones_completadas'] / $stats['total_acciones']) * 100, 2);
        }
        
        // Actualizar el avance en la tabla gap_items
        $updateSql = "UPDATE gap_items 
                      SET avance = :avance, 
                          updated_at = NOW() 
                      WHERE id = :gap_id";
        
        $updateStmt = $this->db->prepare($updateSql);
        $updateStmt->bindValue(':avance', $avance, \PDO::PARAM_STR);
        $updateStmt->bindValue(':gap_id', $gapId, \PDO::PARAM_INT);
        $updateStmt->execute();
    }
}
