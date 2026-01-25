<div class="card card-requerimientos">
    <div class="card-header">
        <h3 class="card-title">Requerimientos Obligatorios</h3>
        <a href="/requerimientos" class="card-link">Ver todos</a>
    </div>
    <div class="card-body">
        <div class="metric-row">
            <span class="metric-label">Total requerimientos:</span>
            <span class="metric-value"><?= $requerimientos['total'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Pendientes:</span>
            <span class="metric-value"><?= $requerimientos['pendientes'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">En proceso:</span>
            <span class="metric-value"><?= $requerimientos['en_proceso'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Completados:</span>
            <span class="metric-value"><?= $requerimientos['completados'] ?></span>
        </div>
    </div>
    <div class="card-footer">
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $requerimientos['porcentaje'] ?>%;"></div>
        </div>
        <span class="progress-label"><?= $requerimientos['porcentaje'] ?>% completado</span>
    </div>
</div>
