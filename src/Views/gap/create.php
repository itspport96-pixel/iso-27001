<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Crear Análisis de Brecha (GAP)</h2>

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
    
    <label>Descripción de la Brecha:</label><br>
    <textarea name="brecha" rows="4" cols="60" required></textarea>
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
    
    <label>Fecha Objetivo de Cierre:</label><br>
    <input type="date" name="fecha_objetivo">
    <br><br>
    
    <hr>
    
    <h3>Plan de Acción</h3>
    <div id="acciones">
        <div>
            <label>Acción 1:</label><br>
            <textarea name="accion_descripcion[]" rows="2" cols="60" placeholder="Descripción de la acción" required></textarea><br>
            <label>Responsable:</label>
            <input type="text" name="accion_responsable[]"><br>
            <label>Fecha Compromiso:</label>
            <input type="date" name="accion_fecha[]" required>
            <br><br>
        </div>
    </div>
    
    <button type="button" onclick="agregarAccion()">Agregar Otra Acción</button>
    <br><br>
    
    <hr>
    
    <button type="submit">Crear GAP</button>
    <a href="/gaps">Cancelar</a>
</form>

<script>
let accionCount = 1;

function agregarAccion() {
    accionCount++;
    const div = document.createElement('div');
    div.innerHTML = `
        <label>Acción ${accionCount}:</label><br>
        <textarea name="accion_descripcion[]" rows="2" cols="60" placeholder="Descripción de la acción" required></textarea><br>
        <label>Responsable:</label>
        <input type="text" name="accion_responsable[]"><br>
        <label>Fecha Compromiso:</label>
        <input type="date" name="accion_fecha[]" required>
        <br><br>
    `;
    document.getElementById('acciones').appendChild(div);
}
</script>
