<h2>Evidencias de Controles</h2>

<h3>Estadísticas</h3>
<p>Total evidencias: <?= $estadisticas['total'] ?></p>
<p>Pendientes de validación: <?= $estadisticas['pendientes'] ?></p>
<p>Aprobadas: <?= $estadisticas['aprobadas'] ?></p>
<p>Rechazadas: <?= $estadisticas['rechazadas'] ?></p>
<p>Espacio utilizado: <?= $estadisticas['tamano_total_mb'] ?> MB</p>

<hr>

<p><a href="/evidencias/create">Subir Nueva Evidencia</a></p>

<hr>

<h3>Filtros</h3>
<form method="GET" action="/evidencias">
    <label>Estado de Validación:</label>
    <select name="estado">
        <option value="">Todos</option>
        <option value="pendiente" <?= ($filtro_estado == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
        <option value="aprobada" <?= ($filtro_estado == 'aprobada') ? 'selected' : '' ?>>Aprobada</option>
        <option value="rechazada" <?= ($filtro_estado == 'rechazada') ? 'selected' : '' ?>>Rechazada</option>
    </select>
    
    <button type="submit">Filtrar</button>
    <a href="/evidencias">Limpiar</a>
</form>

<hr>

<h3>Lista de Evidencias</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Control</th>
            <th>Archivo</th>
            <th>Tamaño</th>
            <th>Estado</th>
            <th>Subido Por</th>
            <th>Fecha</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($evidencias)): ?>
            <tr>
                <td colspan="7">No hay evidencias registradas</td>
            </tr>
        <?php else: ?>
            <?php foreach ($evidencias as $evidencia): ?>
                <tr>
                    <td><?= htmlspecialchars($evidencia['codigo']) ?> - <?= htmlspecialchars(substr($evidencia['control_nombre'], 0, 30)) ?>...</td>
                    <td><?= htmlspecialchars($evidencia['nombre_archivo']) ?></td>
                    <td><?= round($evidencia['tamano'] / 1024, 2) ?> KB</td>
                    <td><?= htmlspecialchars(ucfirst($evidencia['estado_validacion'])) ?></td>
                    <td><?= htmlspecialchars($evidencia['subido_por_nombre'] ?? 'Desconocido') ?></td>
                    <td><?= htmlspecialchars($evidencia['created_at']) ?></td>
                    <td>
                        <a href="/evidencias/<?= $evidencia['id'] ?>">Ver</a> |
                        <a href="/evidencias/<?= $evidencia['id'] ?>/download">Descargar</a>
                        <?php if ($evidencia['estado_validacion'] !== 'aprobada'): ?>
                            | <a href="#" onclick="eliminarEvidencia(<?= $evidencia['id'] ?>); return false;">Eliminar</a>
                        <?php else: ?>
                            | <span style="color: #999;">Inmutable</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>

<script>
function eliminarEvidencia(id) {
    if (!confirm('¿Está seguro de eliminar esta evidencia? El archivo será eliminado permanentemente.')) {
        return;
    }
    
    fetch('/evidencias/' + id + '/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf_token: '<?= \App\Middleware\CsrfMiddleware::getToken() ?>'})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('Evidencia eliminada');
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    });
}
</script>
