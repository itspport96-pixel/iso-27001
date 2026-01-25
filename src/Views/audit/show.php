<h2>Detalle de Auditoría</h2>

<h3>Información General</h3>
<p><strong>ID:</strong> <?= htmlspecialchars($log['id']) ?></p>
<p><strong>Fecha/Hora:</strong> <?= htmlspecialchars($log['created_at']) ?></p>
<p><strong>Usuario:</strong> <?= htmlspecialchars($log['usuario_nombre'] ?? 'N/A') ?></p>
<p><strong>Acción:</strong> <?= htmlspecialchars($log['accion']) ?></p>
<p><strong>Tabla:</strong> <?= htmlspecialchars($log['tabla']) ?></p>
<p><strong>Registro ID:</strong> <?= htmlspecialchars($log['registro_id'] ?? 'N/A') ?></p>

<hr>

<h3>Información de Red</h3>
<p><strong>Dirección IP:</strong> <?= htmlspecialchars($log['ip']) ?></p>
<p><strong>User Agent:</strong> <?= htmlspecialchars($log['user_agent'] ?? 'N/A') ?></p>

<hr>

<?php if ($log['datos_previos']): ?>
<h3>Datos Previos</h3>
<pre><?= htmlspecialchars(json_encode(json_decode($log['datos_previos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
<hr>
<?php endif; ?>

<?php if ($log['datos_nuevos']): ?>
<h3>Datos Nuevos</h3>
<pre><?= htmlspecialchars(json_encode(json_decode($log['datos_nuevos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
<hr>
<?php endif; ?>

<p><a href="/audit">Volver a Auditoría</a> | <a href="/dashboard">Dashboard</a></p>
