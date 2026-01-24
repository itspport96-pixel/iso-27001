<?php

namespace App\Models;

use App\Models\Base\Model;

class Evidencia extends Model
{
    protected string $table = 'evidencias';
    protected bool $usesTenant = true;
    protected bool $usesTimestamps = false; // ← CAMBIADO A FALSE
    protected bool $usesSoftDelete = false;

    public function getByControlId(int $controlId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE control_id = :control_id";
        $params = [':control_id' => $controlId];
        
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

    public function getByEstadoValidacion(string $estado): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE estado_validacion = :estado";
        $params = [':estado' => $estado];
        
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

    public function validar(int $id, string $estado, ?string $comentarios = null, ?int $validadoPor = null): bool
    {
        $sql = "UPDATE {$this->table} 
                SET estado_validacion = :estado,
                    comentarios = :comentarios,
                    validado_por = :validado_por,
                    fecha_validacion = NOW()
                WHERE id = :id";
        
        $params = [
            ':id' => $id,
            ':estado' => $estado,
            ':comentarios' => $comentarios,
            ':validado_por' => $validadoPor
        ];
        
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
