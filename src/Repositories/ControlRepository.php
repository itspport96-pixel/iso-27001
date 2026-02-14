<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;
use PDO;

class ControlRepository extends Repository
{
    protected string $table = 'controles';
    protected bool $usesTenant = false;

    public function getAllWithDominio(): array
    {
        $sql = "SELECT c.*, d.codigo as dominio_codigo, d.nombre as dominio_nombre
                FROM {$this->table} c
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                ORDER BY d.codigo ASC, c.codigo ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDominioId(int $dominioId): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE dominio_id = :dominio_id
                ORDER BY codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dominio_id', $dominioId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByKeyword(string $keyword): array
    {
        $sql = "SELECT c.*, d.nombre as dominio_nombre
                FROM {$this->table} c
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE c.codigo LIKE :keyword
                OR c.nombre LIKE :keyword
                OR c.descripcion LIKE :keyword
                ORDER BY c.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':keyword', "%{$keyword}%");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDominios(): array
    {
        $sql  = "SELECT * FROM controles_dominio ORDER BY codigo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchWithPagination(string $keyword = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $sql    = "SELECT c.*, d.codigo as dominio_codigo, d.nombre as dominio_nombre
                FROM {$this->table} c
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                WHERE 1=1";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (c.codigo LIKE :keyword
                      OR c.nombre LIKE :keyword
                      OR c.descripcion LIKE :keyword)";
            $params[':keyword'] = "%{$keyword}%";
        }

        $sql .= " ORDER BY d.codigo ASC, c.codigo ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countSql = "SELECT COUNT(*) as total
                     FROM {$this->table} c
                     WHERE 1=1";

        if (!empty($keyword)) {
            $countSql .= " AND (c.codigo LIKE :keyword
                           OR c.nombre LIKE :keyword
                           OR c.descripcion LIKE :keyword)";
        }

        $countStmt = $this->db->prepare($countSql);

        if (!empty($keyword)) {
            $countStmt->bindValue(':keyword', "%{$keyword}%");
        }

        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        return [
            'items'       => $items,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int)ceil($total / $perPage),
        ];
    }

    /**
     * Devuelve controles aplicables (aplicable=1) para la empresa actual.
     * Usado en el formulario de subida de evidencias.
     */
    public function getAplicables(): array
    {
        $tenantId = TenantContext::getInstance()->getTenant();

        if (!$tenantId) {
            throw new \Exception('Tenant context not set');
        }

        $sql = "SELECT c.id, c.codigo, c.nombre, d.nombre as dominio_nombre
                FROM {$this->table} c
                INNER JOIN controles_dominio d ON c.dominio_id = d.id
                INNER JOIN soa_entries s ON s.control_id = c.id
                WHERE s.aplicable = 1
                AND s.empresa_id = :empresa_id
                ORDER BY d.codigo ASC, c.codigo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un control tiene requerimientos obligatorios vinculados.
     * Si los tiene, no puede marcarse como no aplicable.
     */
    public function controlTieneRequerimientos(int $controlId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM requerimientos_controles
             WHERE control_id = :control_id
             LIMIT 1"
        );
        $stmt->bindValue(':control_id', $controlId, PDO::PARAM_INT);
        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }
}
