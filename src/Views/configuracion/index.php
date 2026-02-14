<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
$tab = $tab ?? 'empresa';
?>

<div class="page-header">
    <h2>Configuracion</h2>
    <p>Administracion de datos de empresa, logo y correo electronico.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="tabs">
    <a href="/configuracion?tab=empresa" class="tab-link <?= $tab === 'empresa' ? 'active' : '' ?>">Datos de Empresa</a>
    <a href="/configuracion?tab=logo"    class="tab-link <?= $tab === 'logo'    ? 'active' : '' ?>">Logo</a>
    <a href="/configuracion?tab=smtp"    class="tab-link <?= $tab === 'smtp'    ? 'active' : '' ?>">Correo SMTP</a>
</div>

<div class="tab-content">

    <?php if ($tab === 'empresa'): ?>
    <!-- ================================================================== -->
    <!-- TAB: DATOS DE EMPRESA                                               -->
    <!-- ================================================================== -->
    <form method="POST" action="/configuracion/empresa">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="form-section">
            <h3>Informacion General</h3>

            <div class="form-group">
                <label for="nombre">Razon Social *</label>
                <input type="text" id="nombre" name="nombre"
                       value="<?= htmlspecialchars($empresa['nombre'] ?? '') ?>"
                       required maxlength="255">
            </div>

            <div class="form-group">
                <label for="ruc">RUC</label>
                <input type="text" id="ruc" name="ruc"
                       value="<?= htmlspecialchars($empresa['ruc'] ?? '') ?>"
                       disabled>
                <small>El RUC no puede modificarse. Contacte al administrador del sistema.</small>
            </div>

            <div class="form-group">
                <label for="sector">Sector / Industria</label>
                <input type="text" id="sector" name="sector"
                       value="<?= htmlspecialchars($empresa['sector'] ?? '') ?>"
                       maxlength="100">
            </div>
        </div>

        <div class="form-section">
            <h3>Contacto</h3>

            <div class="form-group">
                <label for="contacto">Nombre de Contacto</label>
                <input type="text" id="contacto" name="contacto"
                       value="<?= htmlspecialchars($empresa['contacto'] ?? '') ?>"
                       maxlength="255">
            </div>

            <div class="form-group">
                <label for="telefono">Telefono</label>
                <input type="text" id="telefono" name="telefono"
                       value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>"
                       maxlength="20">
            </div>

            <div class="form-group">
                <label for="email">Email Corporativo *</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($empresa['email'] ?? '') ?>"
                       required maxlength="255">
            </div>

            <div class="form-group">
                <label for="direccion">Direccion</label>
                <textarea id="direccion" name="direccion" rows="3" maxlength="500"><?= htmlspecialchars($empresa['direccion'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit">Guardar Datos de Empresa</button>
        </div>
    </form>

    <?php elseif ($tab === 'logo'): ?>
    <!-- ================================================================== -->
    <!-- TAB: LOGO                                                           -->
    <!-- ================================================================== -->
    <div class="form-section">
        <h3>Logo Actual</h3>
        <?php if (!empty($empresa['logo_path'])): ?>
            <div>
                <img src="/<?= htmlspecialchars($empresa['logo_path']) ?>"
                     alt="Logo empresa"
                     style="max-width:200px; max-height:100px; border:1px solid #ccc; padding:8px;">
            </div>
        <?php else: ?>
            <p>No hay logo cargado.</p>
        <?php endif; ?>
    </div>

    <form method="POST" action="/configuracion/empresa" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        <?php
        // Enviamos los mismos datos de empresa para no perderlos al subir logo
        foreach (['nombre','contacto','telefono','email','direccion','sector'] as $campo): ?>
            <input type="hidden" name="<?= $campo ?>" value="<?= htmlspecialchars($empresa[$campo] ?? '') ?>">
        <?php endforeach; ?>

        <div class="form-section">
            <h3>Subir Nuevo Logo</h3>

            <div class="form-group">
                <label for="logo">Imagen (PNG, JPG, GIF, WEBP — max 2MB)</label>
                <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/gif,image/webp">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit">Subir Logo</button>
        </div>
    </form>

    <?php elseif ($tab === 'smtp'): ?>
    <!-- ================================================================== -->
    <!-- TAB: SMTP                                                           -->
    <!-- ================================================================== -->
    <div class="form-section">
        <h3>Configuracion de Correo SMTP</h3>
        <p>Las credenciales se almacenan cifradas en la base de datos.</p>
    </div>

    <form method="POST" action="/configuracion/smtp">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div class="form-section">
            <h3>Servidor</h3>

            <div class="form-group">
                <label for="smtp_host">Host SMTP *</label>
                <input type="text" id="smtp_host" name="smtp_host"
                       value="<?= htmlspecialchars($smtp['smtp_host'] ?? '') ?>"
                       placeholder="smtp.gmail.com" required maxlength="255">
            </div>

            <div class="form-group">
                <label for="smtp_port">Puerto *</label>
                <input type="number" id="smtp_port" name="smtp_port"
                       value="<?= htmlspecialchars($smtp['smtp_port'] ?? '587') ?>"
                       min="1" max="65535" required>
                <small>Comunes: 587 (TLS), 465 (SSL), 25 (sin cifrado)</small>
            </div>

            <div class="form-group">
                <label for="smtp_cifrado">Cifrado</label>
                <select id="smtp_cifrado" name="smtp_cifrado">
                    <option value="tls" <?= ($smtp['smtp_cifrado'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS (recomendado)</option>
                    <option value="ssl" <?= ($smtp['smtp_cifrado'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= ($smtp['smtp_cifrado'] ?? '') === 'none' ? 'selected' : '' ?>>Ninguno</option>
                </select>
            </div>
        </div>

        <div class="form-section">
            <h3>Autenticacion</h3>

            <div class="form-group">
                <label for="smtp_usuario">Usuario *</label>
                <input type="text" id="smtp_usuario" name="smtp_usuario"
                       value="<?= htmlspecialchars($smtp['smtp_usuario'] ?? '') ?>"
                       placeholder="correo@empresa.com" required maxlength="255">
            </div>

            <div class="form-group">
                <label for="smtp_password">Contrasena</label>
                <input type="password" id="smtp_password" name="smtp_password"
                       placeholder="<?= $smtp_tiene_pwd ? 'Dejar vacio para conservar la actual' : 'Ingrese contrasena' ?>"
                       maxlength="255" autocomplete="new-password">
                <?php if ($smtp_tiene_pwd): ?>
                    <small>Ya existe una contrasena guardada. Dejelo vacio para no cambiarla.</small>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-section">
            <h3>Remitente</h3>

            <div class="form-group">
                <label for="smtp_from_email">Email remitente *</label>
                <input type="email" id="smtp_from_email" name="smtp_from_email"
                       value="<?= htmlspecialchars($smtp['smtp_from_email'] ?? '') ?>"
                       required maxlength="255">
            </div>

            <div class="form-group">
                <label for="smtp_from_nombre">Nombre remitente</label>
                <input type="text" id="smtp_from_nombre" name="smtp_from_nombre"
                       value="<?= htmlspecialchars($smtp['smtp_from_nombre'] ?? 'ISO 27001 Platform') ?>"
                       maxlength="255">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="smtp_activo" value="1"
                           <?= ($smtp['smtp_activo'] ?? '0') === '1' ? 'checked' : '' ?>>
                    Habilitar envio de correos
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit">Guardar Configuracion SMTP</button>
            <button type="button" id="btn-test-smtp">Probar Conexion</button>
        </div>
    </form>

    <div id="test-resultado" style="margin-top:12px; display:none;"></div>

    <script>
    (function () {
        var btn = document.getElementById('btn-test-smtp');
        var resultado = document.getElementById('test-resultado');
        if (!btn) return;

        btn.addEventListener('click', function () {
            btn.disabled = true;
            btn.textContent = 'Probando...';
            resultado.style.display = 'none';

            fetch('/configuracion/smtp/test', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({csrf_token: '<?= $csrfToken ?>'})
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                resultado.style.display = 'block';
                if (data.success) {
                    resultado.textContent = 'Conexion exitosa con el servidor SMTP.';
                    resultado.className = 'alert alert-success';
                } else {
                    resultado.textContent = 'Error: ' + (data.error || 'No se pudo conectar.');
                    resultado.className = 'alert alert-error';
                }
            })
            .catch(function () {
                resultado.style.display = 'block';
                resultado.textContent = 'Error de conexion con el servidor.';
                resultado.className = 'alert alert-error';
            })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = 'Probar Conexion';
            });
        });
    }());
    </script>

    <?php endif; ?>

</div>
