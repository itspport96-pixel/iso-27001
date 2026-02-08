<?php include dirname(__DIR__) . '/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include dirname(__DIR__) . '/layouts/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Editar Perfil</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="/perfil" class="btn btn-secondary">
                        Volver
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Información Personal</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/perfil/update">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required maxlength="255">
                                    <div class="form-text">Mínimo 3 caracteres</div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required maxlength="255">
                                    <div class="form-text">Debe ser único dentro de la empresa</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Rol</label>
                                    <div>
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
                                    <div class="form-text text-muted">El rol no puede ser modificado desde el perfil</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Empresa</label>
                                    <div>
                                        <?= htmlspecialchars($usuario['empresa_nombre']) ?>
                                        <br>
                                        <small class="text-muted">RUC: <?= htmlspecialchars($usuario['empresa_ruc']) ?></small>
                                    </div>
                                    <div class="form-text text-muted">La empresa no puede ser modificada</div>
                                </div>

                                <div class="alert alert-info">
                                    <strong>Nota:</strong> Solo puedes modificar tu nombre y email. Para cambiar tu contraseña, utiliza la opción "Cambiar Contraseña" en el menú.
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="/perfil" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Información de Cuenta</h5>
                        </div>
                        <div class="card-body">
                            <dl>
                                <dt>ID de Usuario</dt>
                                <dd><?= htmlspecialchars($usuario['id']) ?></dd>

                                <dt>Estado</dt>
                                <dd>
                                    <?php
                                    $estados = [
                                        'activo' => '<span class="badge bg-success">Activo</span>',
                                        'inactivo' => '<span class="badge bg-warning">Inactivo</span>',
                                        'bloqueado' => '<span class="badge bg-danger">Bloqueado</span>'
                                    ];
                                    echo $estados[$usuario['estado']] ?? htmlspecialchars($usuario['estado']);
                                    ?>
                                </dd>

                                <dt>Fecha de Registro</dt>
                                <dd><?= date('d/m/Y H:i', strtotime($usuario['created_at'])) ?></dd>

                                <dt>Última Actualización</dt>
                                <dd><?= $usuario['updated_at'] ? date('d/m/Y H:i', strtotime($usuario['updated_at'])) : 'N/A' ?></dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Otras Acciones</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="/perfil/password" class="btn btn-warning">
                                    Cambiar Contraseña
                                </a>
                                <a href="/perfil" class="btn btn-secondary">
                                    Ver Mi Perfil
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Restricciones</h5>
                        </div>
                        <div class="card-body">
                            <ul class="small">
                                <li>No puedes cambiar tu rol</li>
                                <li>No puedes cambiar tu empresa</li>
                                <li>No puedes cambiar tu estado</li>
                                <li>El email debe ser único en tu empresa</li>
                                <li>Los cambios quedan registrados en auditoría</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include dirname(__DIR__) . '/layouts/footer.php'; ?>
