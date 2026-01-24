<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class RequerimientoRepository extends Repository
{
    protected string $table = 'empresa_requerimientos';
    protected bool $usesTenant = true;

    public function getWithRequerimientoBase(): array
    {
        $sql = "SELECT er.*, rb.numero, rb.identificador, rb.titulo, rb.descripcion
                FROM {$this->table} er
                INNER JOIN requerimientos_base rb ON er.requerimiento_id = rb.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE er.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY rb.numero ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT er.*, rb.numero, rb.identificador, rb.titulo, rb.descripcion
                FROM {$this->table} er
                INNER JOIN requerimientos_base rb ON er.requerimiento_id = rb.id
                WHERE er.id = :id";
        
        $params = [':id' => $id];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND er.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getControlesAsociados(int $requerimientoId): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        
        $sql = "SELECT c.id, c.codigo, c.nombre, c.descripcion,
                d.nombre as dominio_nombre,
                s.estado as estado_implementacion,
                s.aplicable
                FROM requerimientos_controles rc
                INNER JOIN controles c ON rc.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN soa_entries s ON c.id = s.control_id AND s.empresa_id = :empresa_id
                WHERE rc.requerimiento_base_id = :requerimiento_id
                ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':requerimiento_id', $requerimientoId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticas(): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados
                FROM {$this->table}";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['total'] > 0) {
            $result['porcentaje'] = round(($result['completados'] / $result['total']) * 100, 2);
        } else {
            $result['porcentaje'] = 0;
        }
        
        return $result;
    }

    public function calcularProgresoRequerimiento(int $requerimientoId): array
    {
        $controles = $this->getControlesAsociados($requerimientoId);
        
        $total = count($controles);
        $aplicables = 0;
        $implementados = 0;
        
        foreach ($controles as $control) {
            if ($control['aplicable'] == 1) {
                $aplicables++;
                if ($control['estado_implementacion'] === 'implementado') {
                    $implementados++;
                }
            }
        }
        
        $porcentaje = $aplicables > 0 ? round(($implementados / $aplicables) * 100, 2) : 0;
        
        return [
            'total_controles' => $total,
            'controles_aplicables' => $aplicables,
            'controles_implementados' => $implementados,
            'porcentaje' => $porcentaje
        ];
    }
}
