<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Mi Perfil</h2>

<h3>Información de la Cuenta</h3>
<p><strong>Rol:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $usuario['rol']))) ?></p>
<p><strong>Estado:</strong> <?= htmlspecialchars(ucfirst($usuario['estado'])) ?></p>
<p><strong>Último Acceso:</strong> <?= htmlspecialchars($usuario['ultimo_acceso'] ?? 'Nunca') ?></p>
<p><strong>Cuenta Creada:</strong> <?= htmlspecialchars($usuario['created_at']) ?></p>

<hr>

<h3>Datos Personales</h3>
<form id="formDatos">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Nombre Completo:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required minlength="3" maxlength="255" style="width: 300px;">
    <br><br>
    
    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required style="width: 300px;">
    <br><br>
    
    <button type="submit">Actualizar Datos</button>
</form>

<div id="messageDatos"></div>

<hr>

<h3>Cambiar Contraseña</h3>
<form id="formPassword">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Contraseña Actual:</label><br>
    <input type="password" name="password_actual" required style="width: 300px;">
    <br><br>
    
    <label>Nueva Contraseña:</label><br>
    <input type="password" name="password_nueva" required minlength="8" style="width: 300px;">
    <br><small>Mínimo 8 caracteres</small>
    <br><br>
    
    <label>Confirmar Nueva Contraseña:</label><br>
    <input type="password" name="password_confirmar" required minlength="8" style="width: 300px;">
    <br><br>
    
    <button type="submit">Cambiar Contraseña</button>
</form>

<div id="messagePassword"></div>

<hr>

<p><a href="/dashboard">Volver al Dashboard</a></p>

<script>
document.getElementById('formDatos').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const button = this.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('messageDatos');
    
    button.disabled = true;
    button.textContent = 'Actualizando...';
    messageDiv.innerHTML = '';
    
    fetch('/perfil/update-datos', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            messageDiv.innerHTML = '<p style="color: green;">' + result.message + '</p>';
            setTimeout(() => window.location.reload(), 1500);
        } else {
            messageDiv.innerHTML = '<p style="color: red;">Error: ' + (result.error || 'Error desconocido') + '</p>';
            button.disabled = false;
            button.textContent = 'Actualizar Datos';
        }
    })
    .catch(err => {
        messageDiv.innerHTML = '<p style="color: red;">Error de conexión</p>';
        button.disabled = false;
        button.textContent = 'Actualizar Datos';
    });
});

document.getElementById('formPassword').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const button = this.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('messagePassword');
    
    if (data.password_nueva !== data.password_confirmar) {
        messageDiv.innerHTML = '<p style="color: red;">Las contraseñas nuevas no coinciden</p>';
        return;
    }
    
    button.disabled = true;
    button.textContent = 'Cambiando...';
    messageDiv.innerHTML = '';
    
    fetch('/perfil/update-password', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            messageDiv.innerHTML = '<p style="color: green;">' + result.message + '</p>';
            this.reset();
            button.disabled = false;
            button.textContent = 'Cambiar Contraseña';
        } else {
            messageDiv.innerHTML = '<p style="color: red;">Error: ' + (result.error || 'Error desconocido') + '</p>';
            button.disabled = false;
            button.textContent = 'Cambiar Contraseña';
        }
    })
    .catch(err => {
        messageDiv.innerHTML = '<p style="color: red;">Error de conexión</p>';
        button.disabled = false;
        button.textContent = 'Cambiar Contraseña';
    });
});
</script>
