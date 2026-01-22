<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Base\Repository;

final class ControlRepository extends Repository
{
    protected string $table = 'controles';

    public function listWithDominio(): array
    {
        $sql = 'SELECT c.id, c.codigo, c.nombre, c.descripcion, d.nombre AS dominio
                FROM controles c
                JOIN controles_dominio d ON d.id = c.dominio_id
                ORDER BY c.codigo';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function findByCodigo(string $codigo): ?array
    {
        $sql = 'SELECT * FROM controles WHERE codigo = :codigo LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch() ?: null;
    }
}
