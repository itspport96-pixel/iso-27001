<div class="card card-controles">
    <div class="card-header">
        <h3 class="card-title">Controles ISO 27001</h3>
        <a href="/controles" class="card-link">Ver todos</a>
    </div>
    <div class="card-body">
        <div class="metric-row">
            <span class="metric-label">Total de controles:</span>
            <span class="metric-value"><?= $controles['total'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Controles NO aplicables:</span>
            <span class="metric-value"><?= $controles['no_aplicables'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Controles aplicables:</span>
            <span class="metric-value"><?= $controles['aplicables'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Implementados:</span>
            <span class="metric-value"><?= $controles['implementados'] ?> (<?= $controles['porcentaje'] ?>%)</span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Parciales:</span>
            <span class="metric-value"><?= $controles['parciales'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">No implementados:</span>
            <span class="metric-value"><?= $controles['no_implementados'] ?></span>
        </div>
    </div>
    <div class="card-footer">
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $controles['porcentaje'] ?>%;"></div>
        </div>
        <span class="progress-label"><?= $controles['porcentaje'] ?>% completado</span>
    </div>
</div>
