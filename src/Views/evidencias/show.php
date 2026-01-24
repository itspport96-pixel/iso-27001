<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Detalle de Evidencia</h2>

<h3>Información del Control</h3>
<p><strong>Código:</strong> <?= htmlspecialchars($evidencia['codigo']) ?></p>
<p><strong>Control:</strong> <?= htmlspecialchars($evidencia['control_nombre']) ?></p>
<p><strong>Dominio:</strong> <?= htmlspecialchars($evidencia['dominio_nombre']) ?></p>

<hr>

<h3>Información del Archivo</h3>
<p><strong>Nombre:</strong> <?= htmlspecialchars($evidencia['nombre_archivo']) ?></p>
<p><strong>Tipo:</strong> <?= htmlspecialchars($evidencia['tipo_mime']) ?></p>
<p><strong>Tamaño:</strong> <?= round($evidencia['tamano'] / 1024, 2) ?> KB</p>
<p><strong>Hash SHA256:</strong> <code><?= htmlspecialchars($evidencia['hash_sha256']) ?></code></p>

<hr>

<h3>Estado de Validación</h3>
<p><strong>Estado:</strong> <?= htmlspecialchars(ucfirst($evidencia['estado_validacion'])) ?></p>

<?php if ($evidencia['estado_validacion'] === 'aprobada'): ?>
    <p style="background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50;">
        ✓ <strong>Evidencia Aprobada:</strong> Este documento es inmutable por cumplimiento normativo ISO 27001. 
        No puede ser editado ni eliminado. Para actualizar, suba una nueva versión.
    </p>
<?php endif; ?>

<?php if ($evidencia['validado_por']): ?>
    <p><strong>Validado por:</strong> <?= htmlspecialchars($evidencia['validado_por_nombre']) ?> (<?= htmlspecialchars($evidencia['validado_por_email']) ?>)</p>
    <p><strong>Fecha de validación:</strong> <?= htmlspecialchars($evidencia['fecha_validacion']) ?></p>
<?php endif; ?>

<?php if ($evidencia['comentarios']): ?>
    <p><strong>Comentarios:</strong><br><?= nl2br(htmlspecialchars($evidencia['comentarios'])) ?></p>
<?php endif; ?>

<hr>

<h3>Información de Carga</h3>
<p><strong>Subido por:</strong> <?= htmlspecialchars($evidencia['subido_por_nombre']) ?> (<?= htmlspecialchars($evidencia['subido_por_email']) ?>)</p>
<p><strong>Fecha de carga:</strong> <?= htmlspecialchars($evidencia['created_at']) ?></p>

<hr>

<h3>Acciones</h3>
<p><a href="/evidencias/<?= $evidencia['id'] ?>/download">Descargar Archivo</a></p>

<?php if ($evidencia['estado_validacion'] === 'pendiente'): ?>
    <hr>
    <h3>Validar Evidencia</h3>
    <p><em>Puede revisar la evidencia y decidir aprobarla, rechazarla, o dejarla pendiente para revisar después.</em></p>
    
    <form id="validarForm">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
        
        <label>Estado:</label><br>
        <select name="estado_validacion" required>
            <option value="">Seleccione</option>
            <option value="aprobada">Aprobar (la evidencia será inmutable)</option>
            <option value="rechazada">Rechazar (podrá eliminarse y subir nueva)</option>
        </select>
        <br><br>
        
        <label>Comentarios:</label><br>
        <textarea name="comentarios" rows="4" cols="60"></textarea>
        <br><br>
        
        <button type="submit">Validar</button>
        <a href="/evidencias">Cancelar (dejar pendiente)</a>
    </form>
<?php elseif ($evidencia['estado_validacion'] === 'rechazada'): ?>
    <p style="background: #ffebee; padding: 10px; border-left: 4px solid #f44336;">
        ✗ <strong>Evidencia Rechazada:</strong> Puede eliminarse y subir una nueva versión corregida.
    </p>
<?php endif; ?>

<hr>

<p><a href="/evidencias">Volver a la Lista</a> | <a href="/dashboard">Dashboard</a></p>

<div id="message"></div>

<script>
<?php if ($evidencia['estado_validacion'] === 'pendiente'): ?>
document.getElementById('validarForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    const estado = data.estado_validacion;
    let confirmMsg = '';
    
    if (estado === 'aprobada') {
        confirmMsg = '¿Aprobar esta evidencia? Una vez aprobada, NO podrá ser eliminada ni modificada.';
    } else if (estado === 'rechazada') {
        confirmMsg = '¿Rechazar esta evidencia? Podrá ser eliminada y reemplazada por una nueva versión.';
    }
    
    if (!confirm(confirmMsg)) {
        return;
    }
    
    fetch('/evidencias/<?= $evidencia['id'] ?>/validar', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            document.getElementById('message').innerHTML = '<p>Evidencia validada exitosamente</p>';
            setTimeout(() => window.location.reload(), 1500);
        } else {
            document.getElementById('message').innerHTML = '<p>Error: ' + (result.error || 'Error desconocido') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('message').innerHTML = '<p>Error de conexión</p>';
    });
});
<?php endif; ?>
</script>
