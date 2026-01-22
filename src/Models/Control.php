<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Base\Model;

class Control extends Model
{
    protected static string $table = 'controles';

    public int $id;
    public string $codigo;
    public string $nombre;
    public ?string $descripcion;
    public int $dominio_id;
}
