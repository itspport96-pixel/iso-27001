<h2>Análisis de Brechas (GAP Analysis)</h2>

<h3>Estadísticas</h3>
<p>Total GAPs: <?= $estadisticas['total'] ?></p>
<p>Prioridad Alta: <?= $estadisticas['prioridad_alta'] ?></p>
<p>Prioridad Media: <?= $estadisticas['prioridad_media'] ?></p>
<p>Prioridad Baja: <?= $estadisticas['prioridad_baja'] ?></p>
<p>Completados: <?= $estadisticas['completados'] ?></p>
<p>Vencidos: <?= $estadisticas['vencidos'] ?></p>
<p>Avance Promedio: <?= $estadisticas['avance_promedio'] ?>%</p>

<hr>

<p><a href="/gaps/create">Crear Nuevo GAP</a></p>

<hr>

<h3>Filtros</h3>
<form method="GET" action="/gaps">
    <label>Prioridad:</label>
    <select name="prioridad">
        <option value="">Todas</option>
        <option value="alta" <?= ($filtro_prioridad == 'alta') ? 'selected' : '' ?>>Alta</option>
        <option value="media" <?= ($filtro_prioridad == 'media') ? 'selected' : '' ?>>Media</option>
        <option value="baja" <?= ($filtro_prioridad == 'baja') ? 'selected' : '' ?>>Baja</option>
    </select>
    
    <button type="submit">Filtrar</button>
    <a href="/gaps">Limpiar</a>
</form>

<hr>

<h3>Lista de GAPs</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Control</th>
            <th>Brecha</th>
            <th>Impacto</th>
            <th>Prioridad</th>
            <th>Avance</th>
            <th>Acciones</th>
            <th>Fecha Objetivo</th>
            <th>Opciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($gaps)): ?>
            <tr>
                <td colspan="8">No hay GAPs registrados</td>
            </tr>
        <?php else: ?>
            <?php foreach ($gaps as $gap): ?>
                <tr>
                    <td><?= htmlspecialchars($gap['codigo']) ?> - <?= htmlspecialchars(substr($gap['control_nombre'], 0, 30)) ?>...</td>
                    <td><?= htmlspecialchars(substr($gap['brecha'], 0, 50)) ?>...</td>
                    <td><?= htmlspecialchars(ucfirst($gap['impacto'])) ?></td>
                    <td><?= htmlspecialchars(ucfirst($gap['prioridad'])) ?></td>
                    <td><?= number_format($gap['avance'], 2) ?>%</td>
                    <td><?= $gap['acciones_completadas'] ?> / <?= $gap['total_acciones'] ?></td>
                    <td><?= htmlspecialchars($gap['fecha_objetivo'] ?? 'No definida') ?></td>
                    <td>
                        <a href="/gaps/<?= $gap['id'] ?>">Ver</a> |
                        <a href="#" onclick="eliminarGap(<?= $gap['id'] ?>); return false;">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>

<script>
function eliminarGap(id) {
    if (!confirm('¿Está seguro de eliminar este GAP? Esta acción marcará el GAP y sus acciones como eliminadas.')) {
        return;
    }
    
    fetch('/gaps/' + id + '/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({csrf_token: '<?= \App\Middleware\CsrfMiddleware::getToken() ?>'})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            alert('GAP eliminado');
            window.location.reload();
        } else {
            alert('Error: ' + result.error);
        }
    });
}
</script>
