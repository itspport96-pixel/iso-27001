<?php include dirname(__DIR__) . '/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include dirname(__DIR__) . '/layouts/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Detalle del Usuario</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <?php if (in_array($user['rol'], ['super_admin', 'admin_empresa'])): ?>
                            <a href="/usuarios/<?= $usuario['id'] ?>/edit" class="btn btn-warning">
                                Editar
                            </a>
                        <?php endif; ?>
                        <a href="/usuarios" class="btn btn-secondary">
                            Volver
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Información General</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>ID:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= htmlspecialchars($usuario['id']) ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Nombre Completo:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= htmlspecialchars($usuario['nombre']) ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Email:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= htmlspecialchars($usuario['email']) ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Rol:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?php
                                    $roles = [
                                        'super_admin' => '<span class="badge bg-danger">Super Admin</span>',
                                        'admin_empresa' => '<span class="badge bg-primary">Admin Empresa</span>',
                                        'auditor' => '<span class="badge bg-info">Auditor</span>',
                                        'consultor' => '<span class="badge bg-secondary">Consultor</span>'
                                    ];
                                    echo $roles[$usuario['rol']] ?? htmlspecialchars($usuario['rol']);
                                    ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Estado:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?php
                                    $estados = [
                                        'activo' => '<span class="badge bg-success">Activo</span>',
                                        'inactivo' => '<span class="badge bg-warning">Inactivo</span>',
                                        'bloqueado' => '<span class="badge bg-danger">Bloqueado</span>'
                                    ];
                                    echo $estados[$usuario['estado']] ?? htmlspecialchars($usuario['estado']);
                                    ?>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong>Empresa:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?= htmlspecialchars($usuario['empresa_nombre']) ?>
                                    <br>
                                    <small class="text-muted">RUC: <?= htmlspecialchars($usuario['empresa_ruc']) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Permisos del Rol</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $permisos = [
                                'super_admin' => [
                                    'Acceso completo al sistema',
                                    'Gestión de todas las empresas',
                                    'Configuración global del sistema',
                                    'Acceso a todas las funcionalidades'
                                ],
                                'admin_empresa' => [
                                    'Gestión completa de su empresa',
                                    'Crear y gestionar usuarios',
                                    'Gestionar controles ISO 27001',
                                    'Administrar evidencias y documentación',
                                    'Generar reportes y análisis',
                                    'Configuración de la empresa'
                                ],
                                'auditor' => [
                                    'Revisar y evaluar controles',
                                    'Registrar hallazgos y observaciones',
                                    'Generar reportes de auditoría',
                                    'Gestionar planes de acción',
                                    'Ver evidencias y documentación',
                                    'Sin permisos de administración'
                                ],
                                'consultor' => [
                                    'Ver información de controles',
                                    'Consultar evidencias',
                                    'Ver reportes existentes',
                                    'Acceso de solo lectura',
                                    'No puede modificar información'
                                ]
                            ];
                            ?>
                            <ul>
                                <?php foreach ($permisos[$usuario['rol']] ?? [] as $permiso): ?>
                                    <li><?= htmlspecialchars($permiso) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Información de Registro</h5>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>Fecha de Registro</dt>
                                <dd><?= date('d/m/Y H:i:s', strtotime($usuario['created_at'])) ?></dd>

                                <dt>Última Actualización</dt>
                                <dd><?= $usuario['updated_at'] ? date('d/m/Y H:i:s', strtotime($usuario['updated_at'])) : 'N/A' ?></dd>

                                <dt>Último Acceso</dt>
                                <dd><?= $usuario['ultimo_acceso'] ? date('d/m/Y H:i:s', strtotime($usuario['ultimo_acceso'])) : 'Nunca' ?></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Acciones Rápidas</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if (in_array($user['rol'], ['super_admin', 'admin_empresa'])): ?>
                                    <a href="/usuarios/<?= $usuario['id'] ?>/edit" class="btn btn-warning">
                                        Editar Usuario
                                    </a>
                                    
                                    <?php if ($usuario['id'] != $user['id']): ?>
                                        <?php if ($usuario['estado'] === 'activo'): ?>
                                            <button type="button" class="btn btn-outline-warning" onclick="cambiarEstado(<?= $usuario['id'] ?>, 'inactivo')">
                                                Desactivar Usuario
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-success" onclick="cambiarEstado(<?= $usuario['id'] ?>, 'activo')">
                                                Activar Usuario
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="btn btn-outline-danger" onclick="confirmarEliminar(<?= $usuario['id'] ?>)">
                                            Eliminar Usuario
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <a href="/usuarios" class="btn btn-secondary">
                                    Volver al Listado
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function cambiarEstado(id, estado) {
    const estadosTexto = {
        'activo': 'activar',
        'inactivo': 'desactivar',
        'bloqueado': 'bloquear'
    };
    
    if (confirm('¿Está seguro que desea ' + estadosTexto[estado] + ' este usuario?')) {
        fetch('/usuarios/' + id + '/cambiar-estado', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            body: JSON.stringify({ estado: estado })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Estado actualizado exitosamente');
                window.location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error al cambiar estado');
            console.error(error);
        });
    }
}

function confirmarEliminar(id) {
    if (confirm('¿Está seguro que desea eliminar este usuario? Esta acción no se puede deshacer.')) {
        fetch('/usuarios/' + id + '/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario eliminado exitosamente');
                window.location.href = '/usuarios';
            } else {
                alert('Error: ' + data.error);
            }
        })
        .catch(error => {
            alert('Error al eliminar usuario');
            console.error(error);
        });
    }
}
</script>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
