<?php

namespace App\Core;

class TenantContext
{
    private static ?TenantContext $instance = null;
    private ?int $tenantId = null;

    private function __construct() {}

    public static function getInstance(): TenantContext
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setTenant(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function getTenant(): ?int
    {
        return $this->tenantId;
    }

    public function hasTenant(): bool
    {
        return $this->tenantId !== null;
    }

    public function clearTenant(): void
    {
        $this->tenantId = null;
    }

    public function validateTenant(): void
    {
        if (!$this->hasTenant()) {
            throw new \Exception('No tenant context set');
        }
    }

    private function __clone() {}
    public function __wakeup() {}
}
