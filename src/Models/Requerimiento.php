<?php

namespace App\Models;

use App\Models\Base\Model;

class Requerimiento extends Model
{
    protected string $table = 'empresa_requerimientos';
    protected bool $usesTenant = true;
    protected bool $usesTimestamps = true;
    protected bool $usesSoftDelete = false;

    public function getByEstado(string $estado): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE estado = :estado";
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

    public function updateEstado(int $id, string $estado, ?string $observaciones = null): bool
    {
        $data = ['estado' => $estado];
        
        if ($observaciones !== null) {
            $data['observaciones'] = $observaciones;
        }
        
        if ($estado === 'en_proceso' && !$this->get($id)['fecha_inicio']) {
            $data['fecha_inicio'] = date('Y-m-d');
        }
        
        if ($estado === 'completado') {
            $data['fecha_completado'] = date('Y-m-d');
        }
        
        return $this->update($id, $data);
    }

    private function get(int $id): ?array
    {
        return $this->find($id);
    }
}
