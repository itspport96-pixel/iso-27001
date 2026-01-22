<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Base\Repository;

final class EmpresaRepository extends Repository
{
    protected string $table = 'empresas';

    public function existsByRuc(string $ruc): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM empresas WHERE ruc = :ruc LIMIT 1');
        $stmt->execute(['ruc' => $ruc]);
        return (bool)$stmt->fetchColumn();
    }
}
