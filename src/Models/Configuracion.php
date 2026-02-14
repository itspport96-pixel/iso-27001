<?php

namespace App\Models;

class Configuracion
{
    // Claves SMTP validas
    public const SMTP_CLAVES = [
        'smtp_host',
        'smtp_port',
        'smtp_usuario',
        'smtp_password',
        'smtp_cifrado',
        'smtp_from_email',
        'smtp_from_nombre',
        'smtp_activo',
    ];

    // Claves que se almacenan cifradas en BD
    public const CLAVES_CIFRADAS = [
        'smtp_password',
    ];
}
