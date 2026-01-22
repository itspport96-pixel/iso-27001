<?php
declare(strict_types=1);

namespace App\Models\Base;

use App\Core\Database;
use PDO;

abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function getTable(): string
    {
        return static::$table;
    }

    public static function getPrimaryKey(): string
    {
        return static::$primaryKey;
    }

    protected static function db(): PDO
    {
        return Database::getInstance()->getConnection();
    }

    public static function find(int $id, int $empresaId): ?static
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id AND empresa_id = :empresa_id LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id, 'empresa_id' => $empresaId]);
        $data = $stmt->fetch();
        return $data ? self::hydrate($data) : null;
    }

    public static function all(int $empresaId, int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE empresa_id = :empresa_id ORDER BY ' . static::$primaryKey . ' DESC LIMIT :limit OFFSET :offset';
        $stmt = self::db()->prepare($sql);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return array_map(fn($row) => self::hydrate($row), $stmt->fetchAll());
    }

    public static function create(array $data): int
    {
        $cols = implode(', ', array_keys($data));
        $place = ':' . implode(', :', array_keys($data));
        $sql = 'INSERT INTO ' . static::$table . ' (' . $cols . ') VALUES (' . $place . ')';
        $stmt = self::db()->prepare($sql);
        $stmt->execute($data);
        return (int)self::db()->lastInsertId();
    }

    public static function update(int $id, int $empresaId, array $data): int
    {
        $set = [];
        foreach ($data as $col => $val) {
            $set[] = "$col = :$col";
        }
        $set = implode(', ', $set);
        $sql = 'UPDATE ' . static::$table . ' SET ' . $set . ' WHERE ' . static::$primaryKey . ' = :id AND empresa_id = :empresa_id LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(array_merge($data, ['id' => $id, 'empresa_id' => $empresaId]));
        return $stmt->rowCount();
    }

    public static function delete(int $id, int $empresaId): int
    {
        $sql = 'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id AND empresa_id = :empresa_id LIMIT 1';
        $stmt = self::db()->prepare($sql);
        $stmt->execute(['id' => $id, 'empresa_id' => $empresaId]);
        return $stmt->rowCount();
    }

    protected static function hydrate(array $data): static
    {
        $obj = new static();
        foreach ($data as $key => $value) {
            if (property_exists($obj, $key)) {
                $obj->$key = $value;
            }
        }
        return $obj;
    }
}
