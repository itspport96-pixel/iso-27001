<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Registro de Nueva Empresa</h2>

<form id="registerForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <h3>Datos de la Empresa</h3>
    
    <label>Nombre de la Empresa:</label><br>
    <input type="text" name="empresa_nombre" required><br><br>
    
    <label>RUC:</label><br>
    <input type="text" name="empresa_ruc" required><br><br>
    
    <label>Email de Contacto:</label><br>
    <input type="email" name="empresa_email" required><br><br>
    
    <label>Contacto:</label><br>
    <input type="text" name="empresa_contacto"><br><br>
    
    <label>Teléfono:</label><br>
    <input type="text" name="empresa_telefono"><br><br>
    
    <label>Dirección:</label><br>
    <textarea name="empresa_direccion"></textarea><br><br>
    
    <hr>
    
    <h3>Datos del Administrador</h3>
    
    <label>Nombre Completo:</label><br>
    <input type="text" name="usuario_nombre" required><br><br>
    
    <label>Email:</label><br>
    <input type="email" name="usuario_email" required><br><br>
    
    <label>Contraseña:</label><br>
    <input type="password" name="password" required><br><br>
    
    <label>Confirmar Contraseña:</label><br>
    <input type="password" name="password_confirm" required><br><br>
    
    <button type="submit">Registrar</button>
</form>

<br>
<p>¿Ya tienes cuenta? <a href="/login">Iniciar Sesión</a></p>

<div id="message"></div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/register', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            document.getElementById('message').innerHTML = '<p>Registro exitoso. Redirigiendo...</p>';
            setTimeout(() => window.location.href = result.redirect, 1000);
        } else {
            document.getElementById('message').innerHTML = '<p>Error: ' + (result.error || 'Error desconocido') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('message').innerHTML = '<p>Error de conexión</p>';
    });
});
</script>
