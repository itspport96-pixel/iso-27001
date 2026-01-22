<?php
declare(strict_types=1);

namespace App\Models\Base;

use App\Core\TenantContext;

abstract class ScopedModel extends Model
{
    public static function find(int $id): ?static
    {
        return parent::find($id, TenantContext::getTenant());
    }

    public static function all(int $limit = 100, int $offset = 0): array
    {
        return parent::all(TenantContext::getTenant(), $limit, $offset);
    }

    public static function create(array $data): int
    {
        $data['empresa_id'] = TenantContext::getTenant();
        return parent::create($data);
    }

    public static function update(int $id, array $data): int
    {
        return parent::update($id, TenantContext::getTenant(), $data);
    }

    public static function delete(int $id): int
    {
        return parent::delete($id, TenantContext::getTenant());
    }
}
