<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Detalle del Usuario</h2>

<p>
    <a href="/usuarios/<?= $usuario['id'] ?>/edit">Editar</a> | 
    <a href="/usuarios">Volver al Listado</a>
</p>

<hr>

<h3>Informacion General</h3>

<table border="0" cellpadding="5" cellspacing="0">
    <tr>
        <td><strong>ID:</strong></td>
        <td><?= htmlspecialchars($usuario['id']) ?></td>
    </tr>
    <tr>
        <td><strong>Nombre Completo:</strong></td>
        <td><?= htmlspecialchars($usuario['nombre']) ?></td>
    </tr>
    <tr>
        <td><strong>Email:</strong></td>
        <td><?= htmlspecialchars($usuario['email']) ?></td>
    </tr>
    <tr>
        <td><strong>Rol:</strong></td>
        <td>
            <?php
            $roles = [
                'super_admin' => 'Super Admin',
                'admin_empresa' => 'Admin Empresa',
                'auditor' => 'Auditor',
                'consultor' => 'Consultor'
            ];
            echo htmlspecialchars($roles[$usuario['rol']] ?? $usuario['rol']);
            ?>
        </td>
    </tr>
    <tr>
        <td><strong>Estado:</strong></td>
        <td>
            <?php
            $estado = htmlspecialchars($usuario['estado']);
            $color = '';
            if ($usuario['estado'] === 'activo') {
                $color = 'green';
            } elseif ($usuario['estado'] === 'inactivo') {
                $color = 'orange';
            } else {
                $color = 'red';
            }
            echo '<span style="color: ' . $color . ';">' . ucfirst($estado) . '</span>';
            ?>
        </td>
    </tr>
    <tr>
        <td><strong>Empresa:</strong></td>
        <td>
            <?= htmlspecialchars($usuario['empresa_nombre']) ?><br>
            <small>RUC: <?= htmlspecialchars($usuario['empresa_ruc']) ?></small>
        </td>
    </tr>
</table>

<hr>

<h3>Informacion de Registro</h3>

<table border="0" cellpadding="5" cellspacing="0">
    <tr>
        <td><strong>Fecha de Registro:</strong></td>
        <td><?= date('d/m/Y H:i:s', strtotime($usuario['created_at'])) ?></td>
    </tr>
    <tr>
        <td><strong>Ultima Actualizacion:</strong></td>
        <td><?= $usuario['updated_at'] ? date('d/m/Y H:i:s', strtotime($usuario['updated_at'])) : 'N/A' ?></td>
    </tr>
    <tr>
        <td><strong>Ultimo Acceso:</strong></td>
        <td><?= $usuario['ultimo_acceso'] ? date('d/m/Y H:i:s', strtotime($usuario['ultimo_acceso'])) : 'Nunca' ?></td>
    </tr>
</table>

<hr>

<h3>Permisos del Rol</h3>

<?php
$permisos = [
    'super_admin' => [
        'Acceso completo al sistema',
        'Gestion de todas las empresas',
        'Configuracion global del sistema',
        'Acceso a todas las funcionalidades'
    ],
    'admin_empresa' => [
        'Gestion completa de su empresa',
        'Crear y gestionar usuarios',
        'Gestionar controles ISO 27001',
        'Administrar evidencias y documentacion',
        'Generar reportes y analisis',
        'Configuracion de la empresa'
    ],
    'auditor' => [
        'Revisar y evaluar controles',
        'Registrar hallazgos y observaciones',
        'Generar reportes de auditoria',
        'Gestionar planes de accion',
        'Ver evidencias y documentacion',
        'Sin permisos de administracion'
    ],
    'consultor' => [
        'Ver informacion de controles',
        'Consultar evidencias',
        'Ver reportes existentes',
        'Acceso de solo lectura',
        'No puede modificar informacion'
    ]
];
?>

<ul>
    <?php foreach ($permisos[$usuario['rol']] ?? [] as $permiso): ?>
        <li><?= htmlspecialchars($permiso) ?></li>
    <?php endforeach; ?>
</ul>

<hr>

<h3>Acciones</h3>

<?php if ($usuario['id'] != $user_actual['id']): ?>
    <p>
        <button onclick="resetPassword(<?= $usuario['id'] ?>, '<?= htmlspecialchars($usuario['nombre']) ?>', '<?= htmlspecialchars($usuario['email']) ?>')" style="background: #17a2b8; color: white; border: 1px solid #17a2b8; padding: 5px 10px; cursor: pointer;">Reset Password</button>
        
        <?php if ($usuario['estado'] === 'activo'): ?>
            <button onclick="cambiarEstado(<?= $usuario['id'] ?>, 'inactivo')">Desactivar Usuario</button>
        <?php else: ?>
            <button onclick="cambiarEstado(<?= $usuario['id'] ?>, 'activo')">Activar Usuario</button>
        <?php endif; ?>
        
        <button onclick="confirmarEliminar(<?= $usuario['id'] ?>)" style="background: #dc3545; color: white; border: 1px solid #dc3545; padding: 5px 10px; cursor: pointer;">Eliminar Usuario</button>
    </p>
<?php else: ?>
    <p><em>No puedes realizar acciones sobre tu propio usuario.</em></p>
<?php endif; ?>

<div id="message"></div>

<script>
function resetPassword(id, nombre, email) {
    var mensaje = 'Resetear la contrasena del usuario "' + nombre + '"?\n\nSe generara una nueva contrasena aleatoria y se enviara al email: ' + email;
    
    if (confirm(mensaje)) {
        fetch('/usuarios/' + id + '/reset-password', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                csrf_token: '<?= $csrfToken ?>'
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                if (data.password) {
                    alert(data.message + '\n\nNueva contrasena: ' + data.password);
                } else {
                    alert(data.message);
                }
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(function() {
            alert('Error de conexion');
        });
    }
}

function cambiarEstado(id, estado) {
    var estadosTexto = {
        'activo': 'activar',
        'inactivo': 'desactivar',
        'bloqueado': 'bloquear'
    };
    
    if (confirm('Esta seguro que desea ' + estadosTexto[estado] + ' este usuario?')) {
        fetch('/usuarios/' + id + '/cambiar-estado', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({
                csrf_token: '<?= $csrfToken ?>',
                estado: estado
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Estado actualizado exitosamente');
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(function() {
            alert('Error de conexion');
        });
    }
}

function confirmarEliminar(id) {
    if (confirm('Esta seguro que desea eliminar este usuario? Esta accion no se puede deshacer.')) {
        fetch('/usuarios/' + id + '/delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({csrf_token: '<?= $csrfToken ?>'})
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                alert('Usuario eliminado exitosamente');
                window.location.href = '/usuarios';
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(function() {
            alert('Error de conexion');
        });
    }
}
</script>
