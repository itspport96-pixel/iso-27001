<?php

namespace App\Models;

use App\Models\Base\Model;

class Control extends Model
{
    protected string $table = 'controles';
    protected bool $usesTenant = false;
    protected bool $usesTimestamps = false;
    protected bool $usesSoftDelete = false;

    public function findByCodigo(string $codigo): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE codigo = :codigo LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':codigo', $codigo);
        $stmt->execute();
        
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function getByDominio(int $dominioId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE dominio_id = :dominio_id ORDER BY codigo ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dominio_id', $dominioId, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getWithDominio(): array
    {
        $sql = "SELECT c.*, d.codigo as dominio_codigo, d.nombre as dominio_nombre 
                FROM {$this->table} c 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                ORDER BY d.codigo ASC, c.codigo ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function countByDominio(): array
    {
        $sql = "SELECT d.codigo, d.nombre, COUNT(c.id) as total 
                FROM controles_dominio d 
                LEFT JOIN {$this->table} c ON d.id = c.dominio_id 
                GROUP BY d.id 
                ORDER BY d.codigo ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
