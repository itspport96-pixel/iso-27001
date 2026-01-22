<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Base\Repository;

final class SOARepository extends Repository
{
    protected string $table = 'soa_entries';

    public function findByControl(int $empresaId, int $controlId): ?array
    {
        $sql = 'SELECT * FROM soa_entries WHERE empresa_id = :empresa_id AND control_id = :control_id LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['empresa_id' => $empresaId, 'control_id' => $controlId]);
        return $stmt->fetch() ?: null;
    }

    public function listByEmpresa(int $empresaId, int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT s.*, c.codigo, c.nombre, d.nombre AS dominio
                FROM soa_entries s
                JOIN controles c ON c.id = s.control_id
                JOIN controles_dominio d ON d.id = c.dominio_id
                WHERE s.empresa_id = :empresa_id
                ORDER BY c.codigo
                LIMIT :limit OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function updateEvaluation(int $id, int $empresaId, array $data): int
    {
        return $this->update($id, $empresaId, $data);
    }
}
