<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Base\Model;

class SOA extends Model
{
    protected static string $table = 'soa_entries';

    public int $id;
    public int $empresa_id;
    public int $control_id;
    public bool $aplicable;
    public string $estado;          // no_implementado|parcial|implementado
    public ?string $justificacion;
    public string $created_at;
    public string $updated_at;
}
