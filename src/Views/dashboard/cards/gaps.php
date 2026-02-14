<div class="card card-gaps">
    <div class="card-header">
        <h3 class="card-title">Análisis de Brechas (GAP)</h3>
        <a href="/gaps" class="card-link">Ver todos</a>
    </div>
    <div class="card-body">
        <div class="metric-row">
            <span class="metric-label">Total GAPs:</span>
            <span class="metric-value"><?= $gaps['total'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Prioridad Alta:</span>
            <span class="metric-value"><?= $gaps['prioridad_alta'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Prioridad Media:</span>
            <span class="metric-value"><?= $gaps['prioridad_media'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Prioridad Baja:</span>
            <span class="metric-value"><?= $gaps['prioridad_baja'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Completados:</span>
            <span class="metric-value"><?= $gaps['completados'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Vencidos:</span>
            <span class="metric-value"><?= $gaps['vencidos'] ?></span>
        </div>
    </div>
    <div class="card-footer">
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $gaps['avance_promedio'] ?>%;"></div>
        </div>
        <span class="progress-label"><?= $gaps['avance_promedio'] ?>% avance promedio</span>
    </div>
</div>
