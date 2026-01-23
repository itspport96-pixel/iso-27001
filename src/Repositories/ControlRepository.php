<?php

namespace App\Repositories;

use App\Repositories\Base\Repository;
use PDO;

class ControlRepository extends Repository
{
    protected string $table = 'controles';
    protected bool $usesTenant = false;

    public function getAllWithDominio(): array
    {
        $sql = "SELECT c.*, d.codigo as dominio_codigo, d.nombre as dominio_nombre 
                FROM {$this->table} c 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                ORDER BY d.codigo ASC, c.codigo ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDominioId(int $dominioId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE dominio_id = :dominio_id 
                ORDER BY codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':dominio_id', $dominioId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchByKeyword(string $keyword): array
    {
        $sql = "SELECT c.*, d.nombre as dominio_nombre 
                FROM {$this->table} c 
                INNER JOIN controles_dominio d ON c.dominio_id = d.id 
                WHERE c.codigo LIKE :keyword 
                OR c.nombre LIKE :keyword 
                OR c.descripcion LIKE :keyword 
                ORDER BY c.codigo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':keyword', "%{$keyword}%");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllDominios(): array
    {
        $sql = "SELECT * FROM controles_dominio ORDER BY codigo ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
