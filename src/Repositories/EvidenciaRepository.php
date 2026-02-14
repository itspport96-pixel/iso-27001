<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class EvidenciaRepository extends Repository
{
    protected string $table = 'evidencias';
    protected bool $usesTenant = true;

    public function getWithControlInfo(): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $sql = "SELECT e.*, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                u1.nombre as subido_por_nombre,
                u2.nombre as validado_por_nombre
                FROM {$this->table} e
                INNER JOIN controles c ON e.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                LEFT JOIN usuarios u2 ON e.validado_por = u2.id
                WHERE e.empresa_id = :empresa_id
                ORDER BY e.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByControlId(int $controlId): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $sql = "SELECT e.*, u1.nombre as subido_por_nombre, u2.nombre as validado_por_nombre
                FROM {$this->table} e
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                LEFT JOIN usuarios u2 ON e.validado_por = u2.id
                WHERE e.control_id = :control_id
                AND e.empresa_id = :empresa_id
                ORDER BY e.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':control_id', $controlId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEstadoValidacion(string $estado): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $sql = "SELECT e.*, c.codigo, c.nombre as control_nombre,
                u1.nombre as subido_por_nombre
                FROM {$this->table} e
                INNER JOIN controles c ON e.control_id = c.id
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                WHERE e.estado_validacion = :estado
                AND e.empresa_id = :empresa_id
                ORDER BY e.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':estado', $estado);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findWithDetails(int $id): ?array
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $sql = "SELECT e.*, c.codigo, c.nombre as control_nombre, c.descripcion,
                d.nombre as dominio_nombre,
                u1.nombre as subido_por_nombre, u1.email as subido_por_email,
                u2.nombre as validado_por_nombre, u2.email as validado_por_email
                FROM {$this->table} e
                INNER JOIN controles c ON e.control_id = c.id
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN usuarios u1 ON e.subido_por = u1.id
                LEFT JOIN usuarios u2 ON e.validado_por = u2.id
                WHERE e.id = :id
                AND e.empresa_id = :empresa_id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getControlesConEvidencias(): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $sql = "SELECT c.id as control_id, c.codigo, c.nombre as control_nombre,
                d.nombre as dominio_nombre,
                COUNT(e.id) as total_evidencias,
                SUM(CASE WHEN e.estado_validacion = 'aprobada' THEN 1 ELSE 0 END) as evidencias_aprobadas,
                SUM(CASE WHEN e.estado_validacion = 'pendiente' THEN 1 ELSE 0 END) as evidencias_pendientes
                FROM controles c
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                LEFT JOIN evidencias e ON c.id = e.control_id AND e.empresa_id = :empresa_id
                GROUP BY c.id
                ORDER BY d.codigo ASC, c.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstadisticas(): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $sql = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado_validacion = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado_validacion = 'aprobada' THEN 1 ELSE 0 END) as aprobadas,
                SUM(CASE WHEN estado_validacion = 'rechazada' THEN 1 ELSE 0 END) as rechazadas,
                SUM(tamano) as tamano_total
                FROM {$this->table}
                WHERE empresa_id = :empresa_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $result['tamano_total_mb'] = round($result['tamano_total'] / 1048576, 2);
        }

        return $result;
    }

    /**
     * Verifica si un control es aplicable para la empresa actual.
     * Un control vinculado a requerimientos obligatorios siempre es aplicable.
     */
    public function controlEsAplicable(int $controlId): bool
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $stmt = $this->db->prepare(
            "SELECT aplicable FROM soa_entries
             WHERE control_id = :control_id
             AND empresa_id = :empresa_id
             LIMIT 1"
        );
        $stmt->bindValue(':control_id', $controlId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        $soa = $stmt->fetch(PDO::FETCH_ASSOC);

        return $soa && (int)$soa['aplicable'] === 1;
    }

    /**
     * Verifica si todas las evidencias no rechazadas de un control estan aprobadas.
     * Requiere al menos una evidencia aprobada y que no existan pendientes.
     */
    public function todasEvidenciasAprobadas(int $controlId): bool
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $stmt = $this->db->prepare(
            "SELECT
                SUM(CASE WHEN estado_validacion = 'aprobada'  THEN 1 ELSE 0 END) as aprobadas,
                SUM(CASE WHEN estado_validacion = 'pendiente' THEN 1 ELSE 0 END) as pendientes
             FROM {$this->table}
             WHERE control_id = :control_id
             AND empresa_id = :empresa_id"
        );
        $stmt->bindValue(':control_id', $controlId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        return (int)$result['aprobadas'] > 0 && (int)$result['pendientes'] === 0;
    }
}
