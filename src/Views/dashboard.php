<?php
declare(strict_types=1);
\App\Core\Session::start(false);
$userId = (string)\App\Core\Session::get('user_id', 'Invitado');
$rol    = (string)\App\Core\Session::get('rol', 'sin_rol');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Dashboard - ISO27001</title>
</head>
<body>
    <h1>Bienvenido <?= htmlspecialchars($userId) ?> (<?= htmlspecialchars($rol) ?>)</h1>
    <nav>
        <ul>
            <li><a href="/controles">Controles</a></li>
            <li><a href="/gap">GAP Analysis</a></li>
            <li><a href="/evidencias">Evidencias</a></li>
            <li><a href="/requerimientos">Requerimientos</a></li>
        </ul>
    </nav>
    <form method="POST" action="/logout">
        <button type="submit">Cerrar sesión</button>
    </form>
</body>
</html>
