<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class SOARepository extends Repository
{
    protected string $table = 'soa_entries';
    protected bool $usesTenant = true;

    public function searchWithPagination(
        string $searchQuery,
        int $page,
        int $perPage,
        $dominioId = null,
        $estado = null,
        $aplicable = null
    ): array {
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage));
        $offset = ($page - 1) * $perPage;
        
        $whereConditions = [];
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $whereConditions[] = "s.empresa_id = ?";
            $params[] = $tenantId;
        }
        
        if (!empty($searchQuery)) {
            $searchQuery = trim($searchQuery);
            if (strlen($searchQuery) > 255) {
                $searchQuery = substr($searchQuery, 0, 255);
            }
            $whereConditions[] = "(c.codigo LIKE ? OR c.nombre LIKE ? OR c.descripcion LIKE ?)";
            $searchParam = '%' . $searchQuery . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($dominioId) && is_numeric($dominioId)) {
            $whereConditions[] = "c.dominio_id = ?";
            $params[] = (int)$dominioId;
        }
        
        if (!empty($estado) && in_array($estado, ['no_implementado', 'parcial', 'implementado'], true)) {
            $whereConditions[] = "s.estado = ?";
            $params[] = $estado;
        }
        
        if ($aplicable !== null && $aplicable !== '') {
            $aplicableValue = (int)$aplicable;
            if ($aplicableValue === 0 || $aplicableValue === 1) {
                $whereConditions[] = "s.aplicable = ?";
                $params[] = $aplicableValue;
            }
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $countSql = "SELECT COUNT(*) 
                     FROM {$this->table} s 
                     INNER JOIN controles c ON s.control_id = c.id 
                     INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                     {$whereClause}";
        
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        $sql = "SELECT s.id, s.empresa_id, s.control_id, s.aplicable, s.estado, 
                s.justificacion, s.responsable, s.fecha_evaluacion, 
                s.created_at, s.updated_at,
                c.codigo, c.nombre as control_nombre, c.descripcion,
                d.codigo as dominio_codigo, d.nombre as dominio_nombre,
                u.nombre as responsable_nombre
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                LEFT JOIN usuarios u ON s.responsable = u.id
                {$whereClause}
                ORDER BY d.codigo ASC, c.codigo ASC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        
        $executeParams = $params;
        $executeParams[] = $perPage;
        $executeParams[] = $offset;
        
        $stmt->execute($executeParams);
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $lastPage = $total > 0 ? (int)ceil($total / $perPage) : 1;
        
        return [
            'data' => $data,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => $lastPage
        ];
    }

    public function findByControlId(int $controlId): ?array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, c.descripcion, c.objetivo,
                d.nombre as dominio_nombre,
                u.nombre as responsable_nombre
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                LEFT JOIN usuarios u ON s.responsable = u.id
                WHERE s.control_id = ?";
        
        $params = [$controlId];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getEstadisticas(): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN aplicable = 0 THEN 1 ELSE 0 END) as no_aplicables,
                SUM(CASE WHEN aplicable = 1 THEN 1 ELSE 0 END) as aplicables,
                SUM(CASE WHEN aplicable = 1 AND estado = 'implementado' THEN 1 ELSE 0 END) as implementados,
                SUM(CASE WHEN aplicable = 1 AND estado = 'parcial' THEN 1 ELSE 0 END) as parciales,
                SUM(CASE WHEN aplicable = 1 AND estado = 'no_implementado' THEN 1 ELSE 0 END) as no_implementados
                FROM {$this->table}";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE empresa_id = ?";
            $params[] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result && $result['aplicables'] > 0) {
            $result['porcentaje'] = round(($result['implementados'] / $result['aplicables']) * 100, 2);
        } else {
            $result['porcentaje'] = 0;
        }
        
        return $result;
    }

    public function getEstadisticasPorDominio(): array
    {
        $sql = "SELECT 
                d.codigo as dominio_codigo,
                d.nombre as dominio_nombre,
                COUNT(*) as total,
                SUM(CASE WHEN s.aplicable = 1 THEN 1 ELSE 0 END) as aplicables,
                SUM(CASE WHEN s.aplicable = 1 AND s.estado = 'implementado' THEN 1 ELSE 0 END) as implementados
                FROM {$this->table} s
                INNER JOIN controles c ON s.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE s.empresa_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " GROUP BY d.id ORDER BY d.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as &$result) {
            if ($result['aplicables'] > 0) {
                $result['porcentaje'] = round(($result['implementados'] / $result['aplicables']) * 100, 2);
            } else {
                $result['porcentaje'] = 0;
            }
        }
        
        return $results;
    }
}
