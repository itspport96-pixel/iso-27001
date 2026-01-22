<?php
declare(strict_types=1);
\App\Core\Session::start(false);
$token = \App\Core\Session::token();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Login - ISO27001</title>
</head>
<body>
    <h1>Iniciar sesión</h1>
    <form method="POST" action="/login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
        <label>RUC/Empresa ID:
            <input type="number" name="empresa_id" required>
        </label><br>
        <label>Email:
            <input type="email" name="email" required>
        </label><br>
        <label>Contraseña:
            <input type="password" name="password" required minlength="8">
        </label><br>
        <button type="submit">Entrar</button>
    </form>
    <p><a href="/register">¿No tienes cuenta? Regístrate</a></p>
</body>
</html>
