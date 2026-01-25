<div class="card card-evidencias">
    <div class="card-header">
        <h3 class="card-title">Evidencias</h3>
        <a href="/evidencias" class="card-link">Ver todas</a>
    </div>
    <div class="card-body">
        <div class="metric-row">
            <span class="metric-label">Total evidencias:</span>
            <span class="metric-value"><?= $evidencias['total'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Pendientes de validación:</span>
            <span class="metric-value"><?= $evidencias['pendientes'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Aprobadas:</span>
            <span class="metric-value"><?= $evidencias['aprobadas'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Rechazadas:</span>
            <span class="metric-value"><?= $evidencias['rechazadas'] ?></span>
        </div>
        <div class="metric-row">
            <span class="metric-label">Espacio utilizado:</span>
            <span class="metric-value"><?= $evidencias['tamano_total_mb'] ?> MB</span>
        </div>
    </div>
    <div class="card-footer">
        <?php 
        $porcentaje_aprobadas = $evidencias['total'] > 0 
            ? round(($evidencias['aprobadas'] / $evidencias['total']) * 100, 2) 
            : 0;
        ?>
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $porcentaje_aprobadas ?>%;"></div>
        </div>
        <span class="progress-label"><?= $porcentaje_aprobadas ?>% aprobadas</span>
    </div>
</div>
