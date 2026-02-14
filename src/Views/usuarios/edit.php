<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Editar Usuario</h2>

<h3>Información Actual</h3>
<p><strong>Creado:</strong> <?= htmlspecialchars($usuario['created_at']) ?></p>
<p><strong>Última Actualización:</strong> <?= htmlspecialchars($usuario['updated_at']) ?></p>
<p><strong>Último Acceso:</strong> <?= htmlspecialchars($usuario['ultimo_acceso'] ?? 'Nunca') ?></p>

<hr>

<form id="formEditar">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Nombre Completo:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required minlength="3" maxlength="255" style="width: 300px;">
    <br><br>
    
    <label>Email:</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required style="width: 300px;">
    <br><br>
    
    <label>Rol:</label><br>
    <select name="rol" required>
        <option value="admin_empresa" <?= ($usuario['rol'] === 'admin_empresa') ? 'selected' : '' ?>>Administrador de Empresa</option>
        <option value="auditor" <?= ($usuario['rol'] === 'auditor') ? 'selected' : '' ?>>Auditor</option>
        <option value="consultor" <?= ($usuario['rol'] === 'consultor') ? 'selected' : '' ?>>Consultor</option>
    </select>
    <br><br>
    
    <label>Estado:</label><br>
    <select name="estado" required>
        <option value="activo" <?= ($usuario['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
        <option value="bloqueado" <?= ($usuario['estado'] === 'bloqueado') ? 'selected' : '' ?>>Bloqueado</option>
    </select>
    <br><small>Para desactivar temporalmente usa el botón "Desactivar" desde el listado de usuarios</small>
    <br><br>
    
    <button type="submit">Actualizar Usuario</button>
    <a href="/usuarios">Cancelar</a>
</form>

<div id="message"></div>

<hr>

<p style="color: #666;"><small>Nota: La contraseña no se puede editar desde aquí. El usuario debe cambiarla desde su perfil.</small></p>

<script>
document.getElementById('formEditar').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const button = this.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('message');
    
    button.disabled = true;
    button.textContent = 'Actualizando...';
    messageDiv.innerHTML = '';
    
    fetch('/usuarios/<?= $usuario['id'] ?>/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            messageDiv.innerHTML = '<p style="color: green;">' + result.message + '</p>';
            setTimeout(() => window.location.href = '/usuarios', 1500);
        } else {
            messageDiv.innerHTML = '<p style="color: red;">Error: ' + (result.error || 'Error desconocido') + '</p>';
            button.disabled = false;
            button.textContent = 'Actualizar Usuario';
        }
    })
    .catch(err => {
        messageDiv.innerHTML = '<p style="color: red;">Error de conexión</p>';
        button.disabled = false;
        button.textContent = 'Actualizar Usuario';
    });
});
</script>
