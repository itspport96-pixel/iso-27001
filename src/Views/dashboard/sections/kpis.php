<?php
// KPIs Críticos para Auditoría ISO 27001
$cumplimiento = $metricas['controles']['porcentaje'] ?? 0;
$total_controles = $metricas['controles']['total'] ?? 93;
$implementados = $metricas['controles']['implementados'] ?? 0;

$gaps_total = $metricas['gaps']['total'] ?? 0;
$gaps_criticos = 0; // Calcular de datos reales si existe campo 'criticidad'
$gaps_pendientes = $metricas['gaps']['pendientes'] ?? 0;

$evidencias_total = $metricas['evidencias']['total'] ?? 0;
$evidencias_vigentes = $metricas['evidencias']['aprobadas'] ?? 0;
$evidencias_pendientes = $metricas['evidencias']['pendientes'] ?? 0;

$req_total = $metricas['requerimientos']['total'] ?? 7;
$req_completados = $metricas['requerimientos']['completados'] ?? 0;
$req_porcentaje = $metricas['requerimientos']['porcentaje'] ?? 0;

// Estado SGSI (basado en cumplimiento general)
if ($cumplimiento >= 80) {
    $sgsi_estado = 'Maduro';
    $sgsi_color = 'green';
} elseif ($cumplimiento >= 50) {
    $sgsi_estado = 'En desarrollo';
    $sgsi_color = 'yellow';
} else {
    $sgsi_estado = 'Inicial';
    $sgsi_color = 'red';
}
?>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
    <!-- KPI: Cumplimiento Global -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-primary-500">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-10 w-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Cumplimiento</dt>
                        <dd class="flex items-baseline">
                            <div class="text-3xl font-bold text-gray-900"><?= number_format($cumplimiento, 1) ?>%</div>
                        </dd>
                        <dd class="text-xs text-gray-500 mt-1"><?= $implementados ?>/<?= $total_controles ?> controles</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI: Riesgos Altos -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-red-500">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">GAPs Críticos</dt>
                        <dd class="flex items-baseline">
                            <div class="text-3xl font-bold text-gray-900"><?= $gaps_criticos ?></div>
                        </dd>
                        <dd class="text-xs text-gray-500 mt-1"><?= $gaps_pendientes ?> pendientes</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI: GAPs Totales -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-amber-500">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-10 w-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">GAPs Activos</dt>
                        <dd class="flex items-baseline">
                            <div class="text-3xl font-bold text-gray-900"><?= $gaps_total ?></div>
                        </dd>
                        <dd class="text-xs text-gray-500 mt-1"><?= $gaps_total - $gaps_pendientes ?> en progreso</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI: Estado SGSI -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-<?= $sgsi_color ?>-500">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-10 w-10 text-<?= $sgsi_color ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Estado SGSI</dt>
                        <dd class="flex items-baseline">
                            <div class="text-2xl font-bold text-gray-900"><?= $sgsi_estado ?></div>
                        </dd>
                        <dd class="text-xs text-gray-500 mt-1">ISO 27001:2022</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI: Evidencias Vigentes -->
    <div class="bg-white overflow-hidden shadow-sm rounded-lg border-l-4 border-green-500">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">Evidencias</dt>
                        <dd class="flex items-baseline">
                            <div class="text-3xl font-bold text-gray-900"><?= $evidencias_vigentes ?></div>
                        </dd>
                        <dd class="text-xs text-gray-500 mt-1"><?= $evidencias_pendientes ?> pendientes</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
