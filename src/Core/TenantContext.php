<?php
declare(strict_types=1);

namespace App\Core;

final class TenantContext
{
    private static ?int $empresaId = null;

    public static function setTenant(int $empresaId): void
    {
        self::$empresaId = $empresaId;
    }

    public static function getTenant(): ?int
    {
        return self::$empresaId;
    }

    public static function clear(): void
    {
        self::$empresaId = null;
    }

    public static function exists(): bool
    {
        return self::$empresaId !== null;
    }
}
