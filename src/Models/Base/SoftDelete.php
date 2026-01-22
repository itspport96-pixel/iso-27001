<?php
declare(strict_types=1);

namespace App\Models\Base;

trait SoftDelete
{
    public static function findActive(int $id, int $empresaId): ?static
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id AND empresa_id = :empresa_id AND deleted_at IS NULL LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id, 'empresa_id' => $empresaId]);
        $data = $stmt->fetch();
        return $data ? self::hydrate($data) : null;
    }

    public static function allActive(int $empresaId, int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE empresa_id = :empresa_id AND deleted_at IS NULL ORDER BY ' . static::$primaryKey . ' DESC LIMIT :limit OFFSET :offset';
        $stmt = self::db()->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($row) => self::hydrate($row), $stmt->fetchAll());
    }

    public static function softDelete(int $id, int $empresaId): int
    {
        return self::update($id, $empresaId, ['deleted_at' => date('Y-m-d H:i:s')]);
    }
}
