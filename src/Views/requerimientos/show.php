<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Detalle del Requerimiento</h2>

<h3>Información del Requerimiento</h3>
<p><strong>Número:</strong> <?= htmlspecialchars($requerimiento['numero']) ?></p>
<p><strong>Identificador:</strong> <?= htmlspecialchars($requerimiento['identificador']) ?></p>
<p><strong>Título:</strong> <?= htmlspecialchars($requerimiento['titulo']) ?></p>
<p><strong>Descripción:</strong><br><?= nl2br(htmlspecialchars($requerimiento['descripcion'])) ?></p>

<hr>

<h3>Estado del Requerimiento</h3>
<p><strong>Estado:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $requerimiento['estado']))) ?></p>

<?php if ($requerimiento['estado'] === 'completado'): ?>
<p style="background: #e8f5e9; padding: 10px; border-left: 4px solid #4caf50;">
    ✓ <strong>Requerimiento Completado Automáticamente:</strong> Todos los controles aplicables están implementados y cuentan con evidencias aprobadas.
</p>
<?php endif; ?>

<p><strong>Fecha Inicio:</strong> <?= htmlspecialchars($requerimiento['fecha_inicio'] ?? 'No iniciado') ?></p>
<p><strong>Fecha Completado:</strong> <?= htmlspecialchars($requerimiento['fecha_completado'] ?? 'No completado') ?></p>

<?php if ($requerimiento['observaciones']): ?>
    <p><strong>Observaciones:</strong><br><?= nl2br(htmlspecialchars($requerimiento['observaciones'])) ?></p>
<?php endif; ?>

<hr>

<h3>Actualizar Observaciones</h3>
<form id="updateForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Observaciones:</label><br>
    <textarea name="observaciones" rows="4" cols="60"><?= htmlspecialchars($requerimiento['observaciones'] ?? '') ?></textarea>
    <br>
    <small>El estado se actualiza automáticamente según la implementación de controles y evidencias.</small>
    <br><br>
    
    <button type="submit">Actualizar</button>
</form>

<hr>

<h3>Progreso de Controles Asociados</h3>
<p><strong>Total de controles:</strong> <?= $progreso['total_controles'] ?></p>
<p><strong>Controles aplicables:</strong> <?= $progreso['controles_aplicables'] ?></p>
<p><strong>Controles implementados:</strong> <?= $progreso['controles_implementados'] ?></p>
<p><strong>Controles con evidencias aprobadas:</strong> <?= $progreso['controles_con_evidencias'] ?> (<?= $progreso['porcentaje'] ?>%)</p>

<hr>

<h3>Controles Asociados a Este Requerimiento</h3>
<?php if (empty($controles)): ?>
    <p>No hay controles asociados a este requerimiento.</p>
<?php else: ?>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Dominio</th>
                <th>Aplicable</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($controles as $control): ?>
                <tr>
                    <td><?= htmlspecialchars($control['codigo']) ?></td>
                    <td><?= htmlspecialchars($control['nombre']) ?></td>
                    <td><?= htmlspecialchars($control['dominio_nombre']) ?></td>
                    <td><?= $control['aplicable'] ? 'Sí' : 'No' ?></td>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $control['estado_implementacion'] ?? 'No evaluado'))) ?></td>
                    <td>
                        <?php if ($control['soa_id']): ?>
                            <a href="/controles/<?= htmlspecialchars($control['soa_id']) ?>">Gestionar</a>
                        <?php else: ?>
                            <span style="color: #999;">N/A</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<hr>

<p><a href="/requerimientos">Volver a la Lista</a> | <a href="/dashboard">Dashboard</a></p>

<div id="message"></div>

<script>
document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/requerimientos/<?= $requerimiento['id'] ?>/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            document.getElementById('message').innerHTML = '<p>Observaciones actualizadas exitosamente</p>';
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
