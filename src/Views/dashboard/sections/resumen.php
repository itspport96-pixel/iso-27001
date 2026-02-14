<section class="section-resumen">
    <h2 class="section-title">Resumen General del Sistema</h2>
    <div class="resumen-grid">
        <div class="resumen-item">
            <span class="resumen-label">Total Controles</span>
            <span class="resumen-value"><?= $controles['total'] ?></span>
        </div>
        <div class="resumen-item">
            <span class="resumen-label">Cumplimiento General</span>
            <span class="resumen-value"><?= $controles['porcentaje'] ?>%</span>
        </div>
        <div class="resumen-item">
            <span class="resumen-label">GAPs Activos</span>
            <span class="resumen-value"><?= $gaps['total'] ?></span>
        </div>
        <div class="resumen-item">
            <span class="resumen-label">Evidencias Aprobadas</span>
            <span class="resumen-value"><?= $evidencias['aprobadas'] ?></span>
        </div>
    </div>
</section>
