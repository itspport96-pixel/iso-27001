<?php
use App\Services\AuthService;

$auth          = new AuthService();
$userActual    = $auth->user();
$empresaNombre = $userActual['empresa_nombre'] ?? 'ISO 27001 Platform';

$roles = [
    'super_admin'   => 'Super Admin',
    'admin_empresa' => 'Admin Empresa',
    'auditor'       => 'Auditor',
    'consultor'     => 'Consultor',
];
$rolLabel = $roles[$userActual['rol'] ?? ''] ?? ($userActual['rol'] ?? '');
?>
<header class="header">
    <div class="header-content">
        <h1 class="header-title"><?= htmlspecialchars($empresaNombre) ?></h1>
        <div class="header-user">
            <span class="user-name"><?= htmlspecialchars($userActual['nombre'] ?? '') ?></span>
            <span class="user-role">(<?= htmlspecialchars($rolLabel) ?>)</span>
            <a href="/logout" class="btn-logout">Cerrar Sesion</a>
        </div>
    </div>
</header>
