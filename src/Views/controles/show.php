<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Detalle del Control</h2>

<h3>Información del Control</h3>
<p><strong>Código:</strong> <?= htmlspecialchars($soa['codigo']) ?></p>
<p><strong>Nombre:</strong> <?= htmlspecialchars($soa['control_nombre']) ?></p>
<p><strong>Dominio:</strong> <?= htmlspecialchars($soa['dominio_nombre']) ?></p>
<p><strong>Descripción:</strong> <?= htmlspecialchars($soa['descripcion']) ?></p>
<p><strong>Objetivo:</strong> <?= htmlspecialchars($soa['objetivo']) ?></p>

<hr>

<h3>Evaluación</h3>

<form id="updateForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>
        <input type="checkbox" name="aplicable" value="1" <?= $soa['aplicable'] ? 'checked' : '' ?>>
        Control Aplicable
    </label>
    <br><br>
    
    <label>Estado de Implementación:</label><br>
    <select name="estado" required>
        <option value="no_implementado" <?= ($soa['estado'] == 'no_implementado') ? 'selected' : '' ?>>No Implementado</option>
        <option value="parcial" <?= ($soa['estado'] == 'parcial') ? 'selected' : '' ?>>Parcial</option>
        <option value="implementado" <?= ($soa['estado'] == 'implementado') ? 'selected' : '' ?>>Implementado</option>
    </select>
    <br><br>
    
    <label>Responsable:</label><br>
    <select name="responsable">
        <option value="">Sin asignar</option>
        <?php foreach ($responsables as $resp): ?>
            <option value="<?= $resp['id'] ?>" <?= ($soa['responsable'] == $resp['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($resp['nombre']) ?> (<?= htmlspecialchars($resp['rol']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>
    
    <label>Justificación / Notas:</label><br>
    <textarea name="justificacion" rows="5" cols="50"><?= htmlspecialchars($soa['justificacion'] ?? '') ?></textarea>
    <br><br>
    
    <button type="submit">Guardar Cambios</button>
</form>

<br>
<p><strong>Responsable Asignado:</strong> <?= htmlspecialchars($soa['responsable_nombre'] ?? 'No asignado') ?></p>
<p><strong>Fecha de Evaluación:</strong> <?= htmlspecialchars($soa['fecha_evaluacion'] ?? 'Nunca') ?></p>
<p><strong>Última Actualización:</strong> <?= htmlspecialchars($soa['updated_at']) ?></p>

<hr>

<p><a href="/controles">Volver a la Lista</a> | <a href="/dashboard">Dashboard</a></p>

<div id="message"></div>

<script>
document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/controles/<?= $soa['id'] ?>/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            document.getElementById('message').innerHTML = '<p>Control actualizado exitosamente</p>';
            setTimeout(() => window.location.reload(), 1500);
        } else {
            document.getElementById('message').innerHTML = '<p>Error: ' + (result.error || 'Error desconocido') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('message').innerHTML = '<p>Error de conexión</p>';
    });
});
</script>
