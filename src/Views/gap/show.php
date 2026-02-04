<?php
use App\Middleware\CsrfMiddleware;
$csrfToken = CsrfMiddleware::getToken();
?>

<h2>Detalle de Brecha (GAP)</h2>

<h3>Informacion del Control</h3>
<p><strong>Codigo:</strong> <?= htmlspecialchars($gap['codigo']) ?></p>
<p><strong>Control:</strong> <?= htmlspecialchars($gap['control_nombre']) ?></p>
<p><strong>Dominio:</strong> <?= htmlspecialchars($gap['dominio_nombre']) ?></p>
<p><strong>Estado del Control:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $gap['control_estado']))) ?></p>

<hr>

<h3>Informacion de la Brecha</h3>
<p><strong>Descripcion:</strong><br><?= nl2br(htmlspecialchars($gap['brecha'])) ?></p>
<p><strong>Impacto:</strong> <?= htmlspecialchars(ucfirst($gap['impacto'])) ?></p>
<p><strong>Prioridad:</strong> <?= htmlspecialchars(ucfirst($gap['prioridad'])) ?></p>
<p><strong>Avance:</strong> <?= number_format($gap['avance'], 2) ?>%</p>
<p><strong>Estado:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $gap['estado_certificacion'] ?? 'abierto'))) ?></p>
<p><strong>Fecha Objetivo:</strong> <?= htmlspecialchars($gap['fecha_objetivo'] ?? 'No definida') ?></p>
<p><strong>Fecha Real de Cierre:</strong> <?= htmlspecialchars($gap['fecha_real_cierre'] ?? 'No cerrado') ?></p>
<p><strong>Creado:</strong> <?= htmlspecialchars($gap['created_at']) ?></p>
<p><strong>Ultima Actualizacion:</strong> <?= htmlspecialchars($gap['updated_at']) ?></p>

<?php if ($gap['estado_certificacion'] === 'pendiente_evidencia'): ?>
<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px;">
    <h4 style="color: #856404; margin-top: 0;">Todas las acciones completadas</h4>
    <p style="color: #856404;">Para cerrar este GAP, debes subir una evidencia que demuestre la implementacion del control <?= htmlspecialchars($gap['codigo']) ?>.</p>
    <a href="/evidencias/create?control_id=<?= htmlspecialchars($gap['control_id']) ?>" 
       style="display: inline-block; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
        Subir Evidencia
    </a>
</div>
<?php endif; ?>

<hr>

<h3>Plan de Accion</h3>
<?php if (empty($acciones)): ?>
    <p>No hay acciones definidas para este GAP.</p>
<?php else: ?>
    <?php foreach ($acciones as $accion): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <h4>Accion #<?= htmlspecialchars($accion['id']) ?></h4>
            <p><strong>Descripcion:</strong> <?= htmlspecialchars($accion['descripcion']) ?></p>
            <p><strong>Responsable:</strong> <?= htmlspecialchars($accion['responsable'] ?? 'No asignado') ?></p>
            <p><strong>Fecha Compromiso:</strong> <?= htmlspecialchars($accion['fecha_compromiso']) ?></p>
            <p><strong>Estado Actual:</strong> <?= htmlspecialchars(ucfirst($accion['estado'])) ?></p>
            <p><strong>Fecha Completado:</strong> <?= htmlspecialchars($accion['fecha_completado'] ?? '-') ?></p>
            
            <?php if (!empty($accion['notas'])): ?>
                <p><strong>Justificacion/Notas:</strong><br><?= nl2br(htmlspecialchars($accion['notas'])) ?></p>
            <?php endif; ?>
            
            <hr>
            
            <form class="accionForm" data-accion-id="<?= htmlspecialchars($accion['id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <label>Estado:</label>
                <select name="estado" required>
                    <option value="pendiente" <?= ($accion['estado'] == 'pendiente') ? 'selected' : '' ?>>Pendiente</option>
                    <option value="en_proceso" <?= ($accion['estado'] == 'en_proceso') ? 'selected' : '' ?>>En Proceso</option>
                    <option value="completada" <?= ($accion['estado'] == 'completada') ? 'selected' : '' ?>>Completada</option>
                    <option value="vencida" <?= ($accion['estado'] == 'vencida') ? 'selected' : '' ?>>Vencida</option>
                </select>
                <br><br>
                
                <label>Justificacion/Notas (que se hizo para mitigar):</label><br>
                <textarea name="notas" rows="3" cols="60"><?= htmlspecialchars($accion['notas'] ?? '') ?></textarea>
                <br><br>
                
                <button type="submit">Actualizar Accion</button>
            </form>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<hr>

<h3>Editar Brecha</h3>
<form id="updateForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    
    <label>Descripcion de la Brecha:</label><br>
    <textarea name="brecha" rows="4" cols="60" required><?= htmlspecialchars($gap['brecha']) ?></textarea>
    <br><br>
    
    <label>Impacto:</label><br>
    <select name="impacto" required>
        <option value="critico" <?= ($gap['impacto'] == 'critico') ? 'selected' : '' ?>>Critico</option>
        <option value="alto" <?= ($gap['impacto'] == 'alto') ? 'selected' : '' ?>>Alto</option>
        <option value="medio" <?= ($gap['impacto'] == 'medio') ? 'selected' : '' ?>>Medio</option>
        <option value="bajo" <?= ($gap['impacto'] == 'bajo') ? 'selected' : '' ?>>Bajo</option>
    </select>
    <br><br>
    
    <label>Prioridad:</label><br>
    <select name="prioridad" required>
        <option value="alta" <?= ($gap['prioridad'] == 'alta') ? 'selected' : '' ?>>Alta</option>
        <option value="media" <?= ($gap['prioridad'] == 'media') ? 'selected' : '' ?>>Media</option>
        <option value="baja" <?= ($gap['prioridad'] == 'baja') ? 'selected' : '' ?>>Baja</option>
    </select>
    <br><br>
    
    <label>Fecha Objetivo:</label><br>
    <input type="date" name="fecha_objetivo" value="<?= htmlspecialchars($gap['fecha_objetivo'] ?? '') ?>">
    <br><br>
    
    <button type="submit">Actualizar GAP</button>
</form>

<hr>

<p><a href="/gaps">Volver a la Lista</a> | <a href="/dashboard">Dashboard</a></p>

<div id="message"></div>

<script>
document.querySelectorAll('.accionForm').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const accionId = this.dataset.accionId;
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        
        fetch('/gaps/accion/' + accionId + '/update', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(data)
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert('Accion actualizada exitosamente');
                window.location.reload();
            } else {
                alert('Error: ' + (result.error || 'Error desconocido'));
            }
        })
        .catch(err => {
            alert('Error de conexion');
        });
    });
});

document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/gaps/<?= htmlspecialchars($gap['id']) ?>/update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            document.getElementById('message').innerHTML = '<p>GAP actualizado exitosamente</p>';
            setTimeout(() => window.location.reload(), 1500);
        } else {
            document.getElementById('message').innerHTML = '<p>Error: ' + (result.error || 'Error desconocido') + '</p>';
        }
    })
    .catch(err => {
        document.getElementById('message').innerHTML = '<p>Error de conexion</p>';
    });
});
</script>
