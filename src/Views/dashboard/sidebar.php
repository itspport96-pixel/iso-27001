<?php
use App\Middleware\RoleMiddleware;

$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = strtok($currentPath, '?');

function isActive($path, $currentPath) {
    if ($path === '/dashboard' && $currentPath === '/dashboard') {
        return 'active';
    }
    if ($path !== '/dashboard' && strpos($currentPath, $path) === 0) {
        return 'active';
    }
    return '';
}
?>

<aside class="sidebar">
    <nav class="sidebar-nav">
        <h3 class="sidebar-title">Modulos</h3>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="/dashboard" class="menu-link <?= isActive('/dashboard', $currentPath) ?>">Dashboard</a>
            </li>

            <?php if (RoleMiddleware::can('controles.view')): ?>
            <li class="menu-item">
                <a href="/controles" class="menu-link <?= isActive('/controles', $currentPath) ?>">Controles ISO 27001</a>
            </li>
            <?php endif; ?>

            <?php if (RoleMiddleware::can('requerimientos.view')): ?>
            <li class="menu-item">
                <a href="/requerimientos" class="menu-link <?= isActive('/requerimientos', $currentPath) ?>">Requerimientos Obligatorios</a>
            </li>
            <?php endif; ?>

            <?php if (RoleMiddleware::can('gaps.view')): ?>
            <li class="menu-item">
                <a href="/gaps" class="menu-link <?= isActive('/gaps', $currentPath) ?>">Analisis de Brechas</a>
            </li>
            <?php endif; ?>

            <?php if (RoleMiddleware::can('evidencias.view')): ?>
            <li class="menu-item">
                <a href="/evidencias" class="menu-link <?= isActive('/evidencias', $currentPath) ?>">Evidencias</a>
            </li>
            <?php endif; ?>

            <?php if (RoleMiddleware::can('audit.view')): ?>
            <li class="menu-item">
                <a href="/audit" class="menu-link <?= isActive('/audit', $currentPath) ?>">Auditoria</a>
            </li>
            <?php endif; ?>
        </ul>

        <?php if (RoleMiddleware::can('usuarios.view')): ?>
        <h3 class="sidebar-title">Administracion</h3>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="/usuarios" class="menu-link <?= isActive('/usuarios', $currentPath) ?>">Usuarios</a>
            </li>
        </ul>
        <?php endif; ?>

        <h3 class="sidebar-title">Cuenta</h3>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="/perfil" class="menu-link <?= isActive('/perfil', $currentPath) ?>">Mi Perfil</a>
            </li>

            <?php if (RoleMiddleware::can('configuracion.manage')): ?>
            <li class="menu-item">
                <a href="/configuracion" class="menu-link <?= isActive('/configuracion', $currentPath) ?>">Configuracion</a>
            </li>
            <li class="menu-item">
                <a href="/notificaciones" class="menu-link <?= isActive('/notificaciones', $currentPath) ?>">Notificaciones</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>
