<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Base\Repository;
use App\Core\TenantContext;

final class UsuarioRepository extends Repository
{
    protected string $table = 'usuarios';

    public function findByEmail(string $email, int $empresaId): ?array
    {
        $sql = 'SELECT * FROM usuarios WHERE email = :email AND empresa_id = :empresa_id AND deleted_at IS NULL LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email, 'empresa_id' => $empresaId]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id, int $empresaId): ?array
    {
        return $this->find($id, $empresaId);
    }

    public function createWithTenant(array $data): int
    {
        $data['empresa_id'] = TenantContext::getTenant();
        return $this->create($data);
    }
}
