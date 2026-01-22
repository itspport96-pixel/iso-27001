<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Iniciar Sesión</h2>

<form id="loginForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>
    
    <label>Contraseña:</label><br>
    <input type="password" name="password" required><br><br>
    
    <button type="submit">Ingresar</button>
</form>

<br>
<p>¿No tienes cuenta? <a href="/register">Registrarse</a></p>

<div id="message"></div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/login', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            document.getElementById('message').innerHTML = '<p>Login exitoso. Redirigiendo...</p>';
            setTimeout(() => window.location.href = result.redirect, 1000);
        } else {
            document.getElementById('message').innerHTML = '<p>Error: ' + (result.error || 'Credenciales inválidas') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('message').innerHTML = '<p>Error de conexión</p>';
    });
});
</script>
