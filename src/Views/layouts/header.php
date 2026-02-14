<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'ISO 27001 Platform' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="/dashboard">
            ISO 27001 Platform
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <div class="dropdown">
                    <a class="nav-link px-3 dropdown-toggle text-white" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nombre'] ?? 'Usuario') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/perfil"><i class="bi bi-person"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="/perfil/password"><i class="bi bi-key"></i> Cambiar Contraseña</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="/logout" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right"></i> Cerrar Sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
