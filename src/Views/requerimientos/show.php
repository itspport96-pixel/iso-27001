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
<p style="background: #e3f2fd; padding: 10px; border-left: 4px solid #2196f3;">
    <strong>Nota:</strong> El estado de este requerimiento se calcula automáticamente basado en el estado de implementación de sus controles asociados.
</p>
<p><strong>Estado:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $requerimiento['estado']))) ?></p>
<p><strong>Fecha Inicio:</strong> <?= htmlspecialchars($requerimiento['fecha_inicio'] ?? 'No iniciado') ?></p>
<p><strong>Fecha Completado:</strong> <?= htmlspecialchars($requerimiento['fecha_completado'] ?? 'No completado') ?></p>

<?php if ($requerimiento['observaciones']): ?>
    <p><strong>Observaciones:</strong><br><?= nl2br(htmlspecialchars($requerimiento['observaciones'])) ?></p>
<?php endif; ?>

<hr>

<h3>Actualizar Observaciones</h3>
<form id="updateForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
    
    <label>Observaciones (mínimo 10 caracteres):</label><br>
    <textarea name="observaciones" rows="4" cols="60" required minlength="10"><?= htmlspecialchars($requerimiento['observaciones'] ?? '') ?></textarea>
    <br><br>
    
    <button type="submit">Actualizar Observaciones</button>
</form>

<hr>

<h3>Progreso de Controles Asociados</h3>
<p><strong>Total de controles:</strong> <?= $progreso['total_controles'] ?></p>
<p><strong>Controles aplicables:</strong> <?= $progreso['controles_aplicables'] ?></p>
<p><strong>Controles implementados:</strong> <?= $progreso['controles_implementados'] ?> (<?= $progreso['porcentaje'] ?>%)</p>

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
                            <a href="/controles/<?= $control['soa_id'] ?>">Editar Control</a>
                        <?php else: ?>
                            <span style="color: #999;">No disponible</span>
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
            document.getElementById('message').innerHTML = '<p style="color: green;">' + result.message + '</p>';
            setTimeout(() => window.location.reload(), 1500);
        } else {
            document.getElementById('message').innerHTML = '<p style="color: red;">Error: ' + (result.error || 'Error desconocido') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('message').innerHTML = '<p style="color: red;">Error de conexión</p>';
    });
});
</script>
