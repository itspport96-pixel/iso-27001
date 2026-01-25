<header class="header">
    <div class="header-content">
        <h1 class="header-title">ISO 27001 Compliance Platform</h1>
        <div class="header-user">
            <span class="user-name"><?= htmlspecialchars($user['nombre']) ?></span>
            <span class="user-role">(<?= htmlspecialchars($user['rol']) ?>)</span>
            <a href="/logout" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
</header>
