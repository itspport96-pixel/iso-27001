<h2>Requerimientos ISO 27001</h2>

<h3>Estadísticas Generales</h3>
<p>Total requerimientos: <?= $estadisticas['total'] ?></p>
<p>Pendientes: <?= $estadisticas['pendientes'] ?></p>
<p>En Proceso: <?= $estadisticas['en_proceso'] ?></p>
<p>Completados: <?= $estadisticas['completados'] ?></p>
<p>Cumplimiento: <?= $estadisticas['porcentaje'] ?>%</p>

<hr>

<h3>7 Requerimientos Obligatorios</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Número</th>
            <th>Identificador</th>
            <th>Título</th>
            <th>Estado</th>
            <th>Progreso Controles</th>
            <th>Fecha Inicio</th>
            <th>Fecha Completado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($requerimientos)): ?>
            <tr>
                <td colspan="8">No hay requerimientos disponibles</td>
            </tr>
        <?php else: ?>
            <?php foreach ($requerimientos as $req): ?>
                <tr>
                    <td><?= htmlspecialchars($req['numero']) ?></td>
                    <td><?= htmlspecialchars($req['identificador']) ?></td>
                    <td><?= htmlspecialchars($req['titulo']) ?></td>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $req['estado']))) ?></td>
                    <td>
                        <?= $req['progreso']['controles_implementados'] ?> / <?= $req['progreso']['controles_aplicables'] ?> 
                        (<?= $req['progreso']['porcentaje'] ?>%)
                    </td>
                    <td><?= htmlspecialchars($req['fecha_inicio'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($req['fecha_completado'] ?? '-') ?></td>
                    <td>
                        <a href="/requerimientos/<?= $req['id'] ?>">Ver Controles</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>
