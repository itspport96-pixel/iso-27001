<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Base\Model;
use App\Models\Base\SoftDelete;

class Empresa extends Model
{
    use SoftDelete;

    protected static string $table = 'empresas';

    public int $id;
    public string $ruc;
    public string $razon_social;
    public ?string $contacto;
    public string $created_at;
    public string $updated_at;
    public ?string $deleted_at = null;
}
