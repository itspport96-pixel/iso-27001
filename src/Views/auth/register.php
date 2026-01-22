<?php
declare(strict_types=1);
\App\Core\Session::start(false); // no regenerar mientras se muestra
$tokenValue = \App\Core\Session::token(); // string a enviar
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registro - ISO27001</title>
</head>
<body>
    <h1>Crear cuenta</h1>
    <form method="POST" action="/register">
        <input type="hidden" name="<?= htmlspecialchars($tokenValue) ?>" value="<?= htmlspecialchars($tokenValue) ?>">
        <label>RUC:
            <input type="text" name="ruc" required maxlength="20">
        </label><br>
        <label>Razón social:
            <input type="text" name="razon_social" required>
        </label><br>
        <label>Email administrador:
            <input type="email" name="email" required>
        </label><br>
        <label>Contraseña:
            <input type="password" name="password" required minlength="8">
        </label><br>
        <label>Confirmar contraseña:
            <input type="password" name="password_confirmation" required minlength="8">
        </label><br>
        <button type="submit">Crear cuenta</button>
    </form>
    <p><a href="/login">¿Ya tienes cuenta? Inicia sesión</a></p>
</body>
</html>
