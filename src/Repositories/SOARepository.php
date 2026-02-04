<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class SOARepository extends Repository
{
    protected string $table = 'soa_entries';
    protected bool $usesTenant = true;

    public function getWithControlInfo(): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, c.descripcion,
                d.codigo as dominio_codigo, d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY d.codigo ASC, c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchWithPagination(
        string $searchQuery,
        int $page,
        int $perPage,
        $dominioId = null,
        $estado = null,
        $aplicable = null
    ): array {
        // Validar y sanitizar inputs
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage));
        $offset = ($page - 1) * $perPage;
        
        // Construir condiciones WHERE
        $conditions = [];
        $params = [];
        $types = [];
        
        // Tenant (siempre presente)
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $conditions[] = "s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        // Búsqueda - sanitizar searchQuery
        if (!empty($searchQuery)) {
            $searchQuery = trim($searchQuery);
            if (strlen($searchQuery) > 255) {
                $searchQuery = substr($searchQuery, 0, 255);
            }
            $conditions[] = "(c.codigo LIKE :search OR c.nombre LIKE :search OR c.descripcion LIKE :search)";
            $params[':search'] = '%' . $searchQuery . '%';
            $types[':search'] = PDO::PARAM_STR;
        }
        
        // Filtro dominio - validar es numérico
        if (!empty($dominioId) && is_numeric($dominioId)) {
            $conditions[] = "c.dominio_id = :dominio_id";
            $params[':dominio_id'] = (int)$dominioId;
            $types[':dominio_id'] = PDO::PARAM_INT;
        }
        
        // Filtro estado - whitelist
        if (!empty($estado) && in_array($estado, ['no_implementado', 'parcial', 'implementado'], true)) {
            $conditions[] = "s.estado = :estado";
            $params[':estado'] = $estado;
            $types[':estado'] = PDO::PARAM_STR;
        }
        
        // Filtro aplicable - validar booleano
        if ($aplicable !== null && $aplicable !== '') {
            $aplicableValue = (int)$aplicable;
            if ($aplicableValue === 0 || $aplicableValue === 1) {
                $conditions[] = "s.aplicable = :aplicable";
                $params[':aplicable'] = $aplicableValue;
                $types[':aplicable'] = PDO::PARAM_INT;
            }
        }
        
        // Construir WHERE clause
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // Count total con prepared statement
        $countSql = "SELECT COUNT(*) 
                     FROM {$this->table} s 
                     INNER JOIN controles c ON s.control_id = c.id 
                     INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                     {$whereClause}";
        
        $countStmt = $this->db->prepare($countSql);
        
        foreach ($params as $key => $value) {
            $countStmt->bindValue($key, $value, $types[$key]);
        }
        
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();
        
        // Query principal con datos y prepared statement
        $sql = "SELECT s.id, s.empresa_id, s.control_id, s.aplicable, s.estado, 
                s.justificacion, s.responsable, s.fecha_evaluacion, 
                s.created_at, s.updated_at,
                c.codigo, c.nombre as control_nombre, c.descripcion,
                d.codigo as dominio_codigo, d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                {$whereClause}
                ORDER BY d.codigo ASC, c.codigo ASC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        
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

    public function getByFiltros($dominioId = null, $estado = null, $aplicable = null): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, 
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE 1=1";
        
        $params = [];
        $types = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        if ($dominioId && is_numeric($dominioId)) {
            $sql .= " AND c.dominio_id = :dominio_id";
            $params[':dominio_id'] = (int)$dominioId;
            $types[':dominio_id'] = PDO::PARAM_INT;
        }
        
        if ($estado && in_array($estado, ['no_implementado', 'parcial', 'implementado'], true)) {
            $sql .= " AND s.estado = :estado";
            $params[':estado'] = $estado;
            $types[':estado'] = PDO::PARAM_STR;
        }
        
        if ($aplicable !== null && $aplicable !== '') {
            $aplicableValue = (int)$aplicable;
            if ($aplicableValue === 0 || $aplicableValue === 1) {
                $sql .= " AND s.aplicable = :aplicable";
                $params[':aplicable'] = $aplicableValue;
                $types[':aplicable'] = PDO::PARAM_INT;
            }
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEstado(string $estado): array
    {
        // Whitelist de estados válidos
        if (!in_array($estado, ['no_implementado', 'parcial', 'implementado'], true)) {
            return [];
        }
        
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, 
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE s.estado = :estado";
        
        $params = [':estado' => $estado];
        $types = [':estado' => PDO::PARAM_STR];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDominio(int $dominioId): array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, 
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE c.dominio_id = :dominio_id";
        
        $params = [':dominio_id' => $dominioId];
        $types = [':dominio_id' => PDO::PARAM_INT];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        $sql .= " ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByControlId(int $controlId): ?array
    {
        $sql = "SELECT s.*, c.codigo, c.nombre as control_nombre, c.descripcion, c.objetivo,
                d.nombre as dominio_nombre 
                FROM {$this->table} s 
                INNER JOIN controles c ON s.control_id = c.id 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE s.control_id = :control_id";
        
        $params = [':control_id' => $controlId];
        $types = [':control_id' => PDO::PARAM_INT];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function updateEstado(int $soaId, string $estado, ?string $justificacion = null): bool
    {
        // Whitelist de estados válidos
        if (!in_array($estado, ['no_implementado', 'parcial', 'implementado'], true)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} 
                SET estado = :estado, 
                    justificacion = :justificacion,
                    fecha_evaluacion = CURDATE(),
                    updated_at = NOW() 
                WHERE id = :id";
        
        $params = [
            ':id' => $soaId,
            ':estado' => $estado,
            ':justificacion' => $justificacion
        ];
        
        $types = [
            ':id' => PDO::PARAM_INT,
            ':estado' => PDO::PARAM_STR,
            ':justificacion' => PDO::PARAM_STR
        ];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        return $stmt->execute();
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
        $types = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        $stmt->execute();
        
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
        $types = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " WHERE s.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
            $types[':empresa_id'] = PDO::PARAM_INT;
        }
        
        $sql .= " GROUP BY d.id ORDER BY d.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, $types[$key]);
        }
        
        $stmt->execute();
        
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
