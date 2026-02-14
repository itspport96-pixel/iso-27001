<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class AuditLogRepository extends Repository
{
    protected string $table = 'audit_logs';
    protected bool $usesTenant = true;

    public function find(int $id): ?array
    {
        $sql = "SELECT al.*, u.nombre as usuario_nombre, u.email as usuario_email
                FROM {$this->table} al
                LEFT JOIN usuarios u ON al.usuario_id = u.id
                WHERE al.id = :id";

        $params = [':id' => $id];

        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND al.empresa_id = :empresa_id";
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

    public function getWithUsuario(array $filtros = [], int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT al.*, u.nombre as usuario_nombre, u.email as usuario_email
                FROM {$this->table} al
                LEFT JOIN usuarios u ON al.usuario_id = u.id
                WHERE 1=1";

        $params = [];

        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND al.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND al.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['accion'])) {
            $sql .= " AND al.accion = :accion";
            $params[':accion'] = strtoupper($filtros['accion']);
        }

        if (!empty($filtros['tabla'])) {
            $sql .= " AND al.tabla = :tabla";
            $params[':tabla'] = $filtros['tabla'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND al.created_at >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND al.created_at <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countWithFiltros(array $filtros = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} al WHERE 1=1";

        $params = [];

        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND al.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }

        if (!empty($filtros['usuario_id'])) {
            $sql .= " AND al.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filtros['usuario_id'];
        }

        if (!empty($filtros['accion'])) {
            $sql .= " AND al.accion = :accion";
            $params[':accion'] = strtoupper($filtros['accion']);
        }

        if (!empty($filtros['tabla'])) {
            $sql .= " AND al.tabla = :tabla";
            $params[':tabla'] = $filtros['tabla'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $sql .= " AND al.created_at >= :fecha_desde";
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $sql .= " AND al.created_at <= :fecha_hasta";
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT al.*, u.nombre as usuario_nombre, u.email as usuario_email
                FROM {$this->table} al
                LEFT JOIN usuarios u ON al.usuario_id = u.id
                WHERE 1=1";

        $params = [];

        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND al.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticas(): array
    {
        $sql = "SELECT
                COUNT(*) as total,
                COUNT(DISTINCT usuario_id) as usuarios_activos,
                COUNT(DISTINCT tabla) as tablas_afectadas
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

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
