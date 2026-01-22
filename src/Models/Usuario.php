<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Base\Model;
use App\Models\Base\SoftDelete;

class Usuario extends Model
{
    use SoftDelete;

    protected static string $table = 'usuarios';

    public int $id;
    public int $empresa_id;
    public string $nombre;
    public string $email;
    public string $password_hash;
    public string $rol;          // super_admin|admin_empresa|auditor|consultor
    public string $estado;        // activo|inactivo
    public string $created_at;
    public string $updated_at;
    public ?string $deleted_at = null;
}
