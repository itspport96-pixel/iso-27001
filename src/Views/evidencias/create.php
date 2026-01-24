<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Subir Nueva Evidencia</h2>

<form method="POST" action="/evidencias/store" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Control:</label><br>
    <select name="control_id" required>
        <option value="">Seleccione un control</option>
        <?php foreach ($controles as $control): ?>
            <option value="<?= $control['id'] ?>">
                <?= htmlspecialchars($control['codigo']) ?> - <?= htmlspecialchars($control['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>
    
    <label>Archivo:</label><br>
    <input type="file" name="archivo" required>
    <br>
    <small>Formatos permitidos: PDF, JPG, PNG, GIF, DOCX, XLSX, TXT. Tamaño máximo: 10MB</small>
    <br><br>
    
    <button type="submit">Subir Evidencia</button>
    <a href="/evidencias">Cancelar</a>
</form>

<?php if (isset($_SESSION['_flash_error'])): ?>
    <p style="color: red;"><?= htmlspecialchars($_SESSION['_flash_error']) ?></p>
    <?php unset($_SESSION['_flash_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['_flash_success'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['_flash_success']) ?></p>
    <?php unset($_SESSION['_flash_success']); ?>
<?php endif; ?>
