<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Gestión de Usuarios</h2>

<p>Usuarios registrados en tu empresa</p>

<p>
    <a href="/usuarios/create">Crear Nuevo Usuario</a>
</p>

<hr>

<h3>Lista de Usuarios</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Último Acceso</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($usuarios)): ?>
            <tr>
                <td colspan="6">No hay usuarios registrados</td>
            </tr>
        <?php else: ?>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $usuario['rol']))) ?></td>
                    <td>
                        <?php if ($usuario['estado'] === 'activo'): ?>
                            <span style="color: green;">Activo</span>
                        <?php elseif ($usuario['estado'] === 'inactivo'): ?>
                            <span style="color: orange;">Inactivo</span>
                        <?php else: ?>
                            <span style="color: red;">Bloqueado</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($usuario['ultimo_acceso'] ?? 'Nunca') ?></td>
                    <td>
                        <?php if ($usuario['id'] === $user_actual['id']): ?>
                            <span style="color: #999;">Tú mismo</span>
                        <?php else: ?>
                            <a href="/usuarios/<?= $usuario['id'] ?>/edit">Editar</a>
                            |
                            <a href="#" onclick="eliminarUsuario(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nombre']) ?>'); return false;">Desactivar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>

<script>
function eliminarUsuario(id, nombre) {
    if (!confirm('¿Desactivar el usuario "' + nombre + '"? El usuario no podrá iniciar sesión pero sus datos se conservarán.')) {
        return;
    }
    
    fetch('/usuarios/' + id + '/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf_token: '<?= $csrfToken ?>'})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    })
    .catch(err => {
        alert('Error de conexión');
    });
}
</script>
