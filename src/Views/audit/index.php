<h2>Registro de Auditoría</h2>

<h3>Estadísticas</h3>
<p>Total de registros: <?= $estadisticas['total'] ?></p>
<p>Usuarios activos: <?= $estadisticas['usuarios_activos'] ?></p>
<p>Tablas afectadas: <?= $estadisticas['tablas_afectadas'] ?></p>

<hr>

<h3>Filtros</h3>
<form method="GET" action="/audit">
    <label>Acción:</label>
    <select name="accion">
        <option value="">Todas</option>
        <option value="insert" <?= ($filtros['accion'] == 'insert') ? 'selected' : '' ?>>INSERT</option>
        <option value="update" <?= ($filtros['accion'] == 'update') ? 'selected' : '' ?>>UPDATE</option>
        <option value="delete" <?= ($filtros['accion'] == 'delete') ? 'selected' : '' ?>>DELETE</option>
    </select>
    
    <label>Tabla:</label>
    <select name="tabla">
        <option value="">Todas</option>
        <option value="soa_entries" <?= ($filtros['tabla'] == 'soa_entries') ? 'selected' : '' ?>>Controles</option>
        <option value="gap_items" <?= ($filtros['tabla'] == 'gap_items') ? 'selected' : '' ?>>GAPs</option>
        <option value="evidencias" <?= ($filtros['tabla'] == 'evidencias') ? 'selected' : '' ?>>Evidencias</option>
        <option value="empresa_requerimientos" <?= ($filtros['tabla'] == 'empresa_requerimientos') ? 'selected' : '' ?>>Requerimientos</option>
    </select>
    
    <label>Fecha Desde:</label>
    <input type="date" name="fecha_desde" value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
    
    <label>Fecha Hasta:</label>
    <input type="date" name="fecha_hasta" value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
    
    <button type="submit">Filtrar</button>
    <a href="/audit">Limpiar</a>
</form>

<hr>

<h3>Logs de Auditoría</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Fecha/Hora</th>
            <th>Usuario</th>
            <th>Acción</th>
            <th>Tabla</th>
            <th>Registro ID</th>
            <th>IP</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($logs)): ?>
            <tr>
                <td colspan="7">No hay registros de auditoría</td>
            </tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['created_at']) ?></td>
                    <td><?= htmlspecialchars($log['usuario_nombre'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($log['accion']) ?></td>
                    <td><?= htmlspecialchars($log['tabla']) ?></td>
                    <td><?= htmlspecialchars($log['registro_id'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($log['ip']) ?></td>
                    <td><a href="/audit/<?= $log['id'] ?>">Ver Detalle</a></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php
    $queryParams = array_filter($filtros);
    
    if ($page > 1):
        $queryParams['page'] = 1;
        $urlFirst = '/audit?' . http_build_query($queryParams);
        
        $queryParams['page'] = $page - 1;
        $urlPrev = '/audit?' . http_build_query($queryParams);
    ?>
        <a href="<?= $urlFirst ?>">Primera</a>
        <a href="<?= $urlPrev ?>">Anterior</a>
    <?php endif; ?>
    
    <span>Página <?= $page ?> de <?= $totalPages ?></span>
    
    <?php if ($page < $totalPages):
        $queryParams['page'] = $page + 1;
        $urlNext = '/audit?' . http_build_query($queryParams);
        
        $queryParams['page'] = $totalPages;
        $urlLast = '/audit?' . http_build_query($queryParams);
    ?>
        <a href="<?= $urlNext ?>">Siguiente</a>
        <a href="<?= $urlLast ?>">Última</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>
