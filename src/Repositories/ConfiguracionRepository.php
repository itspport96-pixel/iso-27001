<?php

namespace App\Repositories;

use App\Core\Database;

class ConfiguracionRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene una configuracion por clave y empresa.
     */
    public function get(int $empresaId, string $clave): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT clave, valor, es_cifrado
             FROM empresa_configuraciones
             WHERE empresa_id = :empresa_id AND clave = :clave
             LIMIT 1'
        );
        $stmt->execute([':empresa_id' => $empresaId, ':clave' => $clave]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Obtiene todas las configuraciones de una empresa.
     */
    public function getAll(int $empresaId): array
    {
        $stmt = $this->db->prepare(
            'SELECT clave, valor, es_cifrado, descripcion
             FROM empresa_configuraciones
             WHERE empresa_id = :empresa_id
             ORDER BY clave ASC'
        );
        $stmt->execute([':empresa_id' => $empresaId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un grupo de claves especificas para una empresa.
     */
    public function getByClaves(int $empresaId, array $claves): array
    {
        if (empty($claves)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($claves), '?'));
        $stmt = $this->db->prepare(
            "SELECT clave, valor, es_cifrado
             FROM empresa_configuraciones
             WHERE empresa_id = ? AND clave IN ($placeholders)"
        );
        $params = array_merge([$empresaId], $claves);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Inserta o actualiza una configuracion (upsert).
     */
    public function set(int $empresaId, string $clave, ?string $valor, int $esCifrado, string $descripcion = ''): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO empresa_configuraciones (empresa_id, clave, valor, es_cifrado, descripcion)
             VALUES (:empresa_id, :clave, :valor, :es_cifrado, :descripcion)
             ON DUPLICATE KEY UPDATE
                valor       = VALUES(valor),
                es_cifrado  = VALUES(es_cifrado),
                descripcion = VALUES(descripcion),
                updated_at  = CURRENT_TIMESTAMP'
        );
        return $stmt->execute([
            ':empresa_id'  => $empresaId,
            ':clave'       => $clave,
            ':valor'       => $valor,
            ':es_cifrado'  => $esCifrado,
            ':descripcion' => $descripcion,
        ]);
    }

    /**
     * Elimina una clave de configuracion.
     */
    public function delete(int $empresaId, string $clave): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM empresa_configuraciones
             WHERE empresa_id = :empresa_id AND clave = :clave'
        );
        return $stmt->execute([':empresa_id' => $empresaId, ':clave' => $clave]);
    }

    /**
     * Obtiene los datos de la empresa desde la tabla empresas.
     */
    public function getEmpresa(int $empresaId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nombre, ruc, contacto, telefono, email, direccion, sector, logo_path
             FROM empresas
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $empresaId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Actualiza los datos de la empresa en la tabla empresas.
     */
    public function updateEmpresa(int $empresaId, array $datos): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE empresas
             SET nombre    = :nombre,
                 contacto  = :contacto,
                 telefono  = :telefono,
                 email     = :email,
                 direccion = :direccion,
                 sector    = :sector
             WHERE id = :id'
        );
        return $stmt->execute([
            ':nombre'    => $datos['nombre'],
            ':contacto'  => $datos['contacto'] ?? null,
            ':telefono'  => $datos['telefono'] ?? null,
            ':email'     => $datos['email'] ?? null,
            ':direccion' => $datos['direccion'] ?? null,
            ':sector'    => $datos['sector'] ?? null,
            ':id'        => $empresaId,
        ]);
    }

    /**
     * Actualiza solo el logo_path en empresas.
     */
    public function updateLogo(int $empresaId, string $logoPath): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE empresas SET logo_path = :logo_path WHERE id = :id'
        );
        return $stmt->execute([':logo_path' => $logoPath, ':id' => $empresaId]);
    }
}
