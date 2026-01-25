<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Crear Nuevo Análisis de Brecha (GAP)</h2>

<?php if (isset($_SESSION['_flash_error'])): ?>
    <p style="color: red;"><?= htmlspecialchars($_SESSION['_flash_error']) ?></p>
    <?php unset($_SESSION['_flash_error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['_flash_success'])): ?>
    <p style="color: green;"><?= htmlspecialchars($_SESSION['_flash_success']) ?></p>
    <?php unset($_SESSION['_flash_success']); ?>
<?php endif; ?>

<form method="POST" action="/gaps/store">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Control:</label><br>
    <select name="soa_id" required>
        <option value="">Seleccione un control</option>
        <?php foreach ($controles as $control): ?>
            <option value="<?= $control['soa_id'] ?>">
                <?= htmlspecialchars($control['codigo']) ?> - <?= htmlspecialchars($control['control_nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>
    
    <label>Descripción de la Brecha (mínimo 10 caracteres):</label><br>
    <textarea name="brecha" rows="4" cols="60" required minlength="10"></textarea>
    <br><br>
    
    <label>Impacto:</label><br>
    <select name="impacto" required>
        <option value="">Seleccione</option>
        <option value="critico">Crítico</option>
        <option value="alto">Alto</option>
        <option value="medio">Medio</option>
        <option value="bajo">Bajo</option>
    </select>
    <br><br>
    
    <label>Prioridad:</label><br>
    <select name="prioridad" required>
        <option value="">Seleccione</option>
        <option value="alta">Alta</option>
        <option value="media">Media</option>
        <option value="baja">Baja</option>
    </select>
    <br><br>
    
    <label>Fecha Objetivo:</label><br>
    <input type="date" name="fecha_objetivo">
    <br><br>
    
    <h3>Acciones Correctivas</h3>
    <div id="acciones-container">
        <div class="accion-item">
            <label>Descripción:</label><br>
            <input type="text" name="accion_descripcion[]" style="width: 400px;">
            <br>
            <label>Responsable:</label><br>
            <input type="text" name="accion_responsable[]" style="width: 200px;">
            <br>
            <label>Fecha Compromiso:</label><br>
            <input type="date" name="accion_fecha[]">
            <br><br>
        </div>
    </div>
    
    <button type="button" onclick="agregarAccion()">Agregar Otra Acción</button>
    <br><br>
    
    <button type="submit">Crear GAP</button>
    <a href="/gaps">Cancelar</a>
</form>

<script>
function agregarAccion() {
    const container = document.getElementById('acciones-container');
    const nuevaAccion = document.createElement('div');
    nuevaAccion.className = 'accion-item';
    nuevaAccion.innerHTML = `
        <label>Descripción:</label><br>
        <input type="text" name="accion_descripcion[]" style="width: 400px;">
        <br>
        <label>Responsable:</label><br>
        <input type="text" name="accion_responsable[]" style="width: 200px;">
        <br>
        <label>Fecha Compromiso:</label><br>
        <input type="date" name="accion_fecha[]">
        <br><br>
    `;
    container.appendChild(nuevaAccion);
}
</script>
