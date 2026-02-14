<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class UsuarioRepository extends Repository
{
    protected string $table = 'usuarios';
    protected bool $usesTenant = true;

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT u.*, e.nombre as empresa_nombre, e.ruc as empresa_ruc
                FROM {$this->table} u
                INNER JOIN empresas e ON u.empresa_id = e.id
                WHERE u.email = :email
                AND u.deleted_at IS NULL";
        
        $params = [':email' => $email];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND u.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findWithEmpresa(int $id): ?array
    {
        $sql = "SELECT u.*, e.nombre as empresa_nombre, e.ruc as empresa_ruc
                FROM {$this->table} u
                INNER JOIN empresas e ON u.empresa_id = e.id
                WHERE u.id = :id
                AND u.deleted_at IS NULL";
        
        $params = [':id' => $id];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND u.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getAllWithEmpresa(): array
    {
        $sql = "SELECT u.*, e.nombre as empresa_nombre
                FROM {$this->table} u
                INNER JOIN empresas e ON u.empresa_id = e.id
                WHERE u.deleted_at IS NULL";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND u.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchWithPagination(string $busqueda = '', int $page = 1, int $perPage = 10): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT u.*, e.nombre as empresa_nombre
                FROM {$this->table} u
                INNER JOIN empresas e ON u.empresa_id = e.id
                WHERE u.deleted_at IS NULL
                AND u.empresa_id = ?";

        $params = [$tenantId];

        if (!empty($busqueda)) {
            $busqueda = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busqueda);
            $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
            $searchParam = '%' . $busqueda . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countWithBusqueda(string $busqueda = ''): int
    {
        $tenantId = TenantContext::getInstance()->getTenant();
        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }
        
        $sql = "SELECT COUNT(*) FROM {$this->table} u 
                WHERE u.deleted_at IS NULL 
                AND u.empresa_id = ?";

        $params = [$tenantId];

        if (!empty($busqueda)) {
            $busqueda = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $busqueda);
            $sql .= " AND (u.nombre LIKE ? OR u.email LIKE ?)";
            $searchParam = '%' . $busqueda . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn();
    }

    public function emailExistsInEmpresa(string $email, int $empresaId, ?int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE email = :email
                AND empresa_id = :empresa_id
                AND deleted_at IS NULL";
        
        $params = [
            ':email' => $email,
            ':empresa_id' => $empresaId
        ];
        
        if ($excludeUserId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeUserId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getByRole(string $rol): array
    {
        $sql = "SELECT u.*, e.nombre as empresa_nombre
                FROM {$this->table} u
                INNER JOIN empresas e ON u.empresa_id = e.id
                WHERE u.rol = :rol
                AND u.deleted_at IS NULL";
        
        $params = [':rol' => $rol];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND u.empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY u.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResponsables(): array
    {
        $sql = "SELECT id, nombre, email, rol
                FROM {$this->table}
                WHERE rol IN ('admin_empresa', 'auditor')
                AND estado = 'activo'
                AND deleted_at IS NULL";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $sql .= " ORDER BY nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticas(): array
    {
        $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                SUM(CASE WHEN estado = 'bloqueado' THEN 1 ELSE 0 END) as bloqueados,
                SUM(CASE WHEN rol = 'admin_empresa' THEN 1 ELSE 0 END) as admins,
                SUM(CASE WHEN rol = 'auditor' THEN 1 ELSE 0 END) as auditores,
                SUM(CASE WHEN rol = 'consultor' THEN 1 ELSE 0 END) as consultores
                FROM {$this->table}
                WHERE deleted_at IS NULL";
        
        $params = [];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $sql = "UPDATE {$this->table}
                SET password_hash = :password_hash,
                    updated_at = NOW()
                WHERE id = :id
                AND deleted_at IS NULL";
        
        $params = [
            ':id' => $id,
            ':password_hash' => $passwordHash
        ];
        
        // NO usar tenant para reset de password - el admin puede resetear cualquier usuario de su empresa
        // El tenant ya se valida en el controller
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount() > 0;
    }

    public function updateEstado(int $id, string $estado): bool
    {
        $sql = "UPDATE {$this->table}
                SET estado = :estado,
                    updated_at = NOW()
                WHERE id = :id
                AND deleted_at IS NULL";
        
        $params = [
            ':id' => $id,
            ':estado' => $estado
        ];
        
        if ($this->usesTenant) {
            $tenantId = TenantContext::getInstance()->getTenant();
            $sql .= " AND empresa_id = :empresa_id";
            $params[':empresa_id'] = $tenantId;
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
