<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Crear Nuevo Usuario</h2>

<form id="formCrear">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Nombre Completo:</label><br>
    <input type="text" name="nombre" required minlength="3" maxlength="255" style="width: 300px;">
    <br><br>
    
    <label>Email:</label><br>
    <input type="email" name="email" required style="width: 300px;">
    <br><small>Debe ser único en tu empresa</small>
    <br><br>
    
    <label>Contraseña:</label><br>
    <input type="password" name="password" required minlength="8" style="width: 300px;">
    <br><small>Mínimo 8 caracteres</small>
    <br><br>
    
    <label>Rol:</label><br>
    <select name="rol" required>
        <option value="">Seleccione un rol</option>
        <option value="admin_empresa">Administrador de Empresa</option>
        <option value="auditor">Auditor</option>
        <option value="consultor">Consultor</option>
    </select>
    <br><br>
    
    <div style="background: #e3f2fd; padding: 10px; border-left: 4px solid #2196f3; margin-bottom: 15px;">
        <strong>Permisos por rol:</strong>
        <ul>
            <li><strong>Administrador:</strong> Control total, gestiona usuarios, aprueba evidencias</li>
            <li><strong>Auditor:</strong> Solo lectura y validación de evidencias</li>
            <li><strong>Consultor:</strong> Crear GAPs, subir evidencias, sin permisos de validación</li>
        </ul>
    </div>
    
    <button type="submit">Crear Usuario</button>
    <a href="/usuarios">Cancelar</a>
</form>

<div id="message"></div>

<script>
document.getElementById('formCrear').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const button = this.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('message');
    
    button.disabled = true;
    button.textContent = 'Creando...';
    messageDiv.innerHTML = '';
    
    fetch('/usuarios/store', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            messageDiv.innerHTML = '<p style="color: green;">' + result.message + '</p>';
            setTimeout(() => window.location.href = result.redirect, 1000);
        } else {
            messageDiv.innerHTML = '<p style="color: red;">Error: ' + (result.error || 'Error desconocido') + '</p>';
            button.disabled = false;
            button.textContent = 'Crear Usuario';
        }
    })
    .catch(err => {
        messageDiv.innerHTML = '<p style="color: red;">Error de conexión</p>';
        button.disabled = false;
        button.textContent = 'Crear Usuario';
    });
});
</script>
