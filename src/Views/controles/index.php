<h2>Controles ISO 27001</h2>

<h3>Estadísticas Generales</h3>
<p>Total de controles: <?= $estadisticas['total'] ?></p>
<p>Controles aplicables: <?= $estadisticas['aplicables'] ?></p>
<p>Implementados: <?= $estadisticas['implementados'] ?> (<?= $estadisticas['porcentaje'] ?>%)</p>
<p>Parciales: <?= $estadisticas['parciales'] ?></p>
<p>No implementados: <?= $estadisticas['no_implementados'] ?></p>

<hr>

<h3>Filtros</h3>
<form method="GET" action="/controles">
    <label>Dominio:</label>
    <select name="dominio">
        <option value="">Todos</option>
        <?php foreach ($dominios as $dominio): ?>
            <option value="<?= $dominio['id'] ?>" <?= ($filtro_dominio == $dominio['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($dominio['codigo']) ?> - <?= htmlspecialchars($dominio['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    
    <label>Estado:</label>
    <select name="estado">
        <option value="">Todos</option>
        <option value="no_implementado" <?= ($filtro_estado == 'no_implementado') ? 'selected' : '' ?>>No Implementado</option>
        <option value="parcial" <?= ($filtro_estado == 'parcial') ? 'selected' : '' ?>>Parcial</option>
        <option value="implementado" <?= ($filtro_estado == 'implementado') ? 'selected' : '' ?>>Implementado</option>
    </select>
    
    <label>Aplicabilidad:</label>
    <select name="aplicable">
        <option value="">Todos</option>
        <option value="1" <?= ($filtro_aplicable == '1') ? 'selected' : '' ?>>Aplicables</option>
        <option value="0" <?= ($filtro_aplicable == '0') ? 'selected' : '' ?>>No Aplicables</option>
    </select>
    
    <button type="submit">Filtrar</button>
    <a href="/controles">Limpiar</a>
</form>

<hr>

<h3>Lista de Controles</h3>
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
        <?php if (empty($soas)): ?>
            <tr>
                <td colspan="6">No hay controles disponibles</td>
            </tr>
        <?php else: ?>
            <?php foreach ($soas as $soa): ?>
                <tr>
                    <td><?= htmlspecialchars($soa['codigo']) ?></td>
                    <td><?= htmlspecialchars($soa['control_nombre']) ?></td>
                    <td><?= htmlspecialchars($soa['dominio_nombre']) ?></td>
                    <td><?= $soa['aplicable'] ? 'Sí' : 'No' ?></td>
                    <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $soa['estado']))) ?></td>
                    <td>
                        <a href="/controles/<?= $soa['id'] ?>">Ver/Editar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<br>
<p><a href="/dashboard">Volver al Dashboard</a></p>
