<?php
include_once __DIR__ . '/../../components/sparkline.php';

// Preparar datos para gráficos
$controles_implementados = $metricas['controles']['implementados'] ?? 0;
$controles_parciales = $metricas['controles']['parciales'] ?? 0;
$controles_no_implementados = $metricas['controles']['no_implementados'] ?? 0;
$controles_total = $controles_implementados + $controles_parciales + $controles_no_implementados;

// Distribución por dominio
$dominios_data = $metricas['controles_por_dominio'] ?? [];
?>

<div class="mt-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-6">Análisis de Cumplimiento</h2>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Gráfico 1: Distribución de Controles -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Implementación de Controles</h3>
            
            <div class="space-y-4">
                <!-- Implementados -->
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">Implementados</span>
                        <span class="text-gray-900 font-semibold"><?= $controles_implementados ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-green-500 h-3 rounded-full" style="width: <?= $controles_total > 0 ? ($controles_implementados / $controles_total * 100) : 0 ?>%"></div>
                    </div>
                </div>
                
                <!-- Parciales -->
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">Parcialmente Implementados</span>
                        <span class="text-gray-900 font-semibold"><?= $controles_parciales ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-amber-500 h-3 rounded-full" style="width: <?= $controles_total > 0 ? ($controles_parciales / $controles_total * 100) : 0 ?>%"></div>
                    </div>
                </div>
                
                <!-- No Implementados -->
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700">No Implementados</span>
                        <span class="text-gray-900 font-semibold"><?= $controles_no_implementados ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-red-500 h-3 rounded-full" style="width: <?= $controles_total > 0 ? ($controles_no_implementados / $controles_total * 100) : 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráfico 2: Cumplimiento por Dominio -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Cumplimiento por Dominio</h3>
            
            <div class="space-y-3">
                <?php if (empty($dominios_data)): ?>
                    <p class="text-sm text-gray-500 text-center py-4">No hay datos disponibles</p>
                <?php else: ?>
                    <?php foreach (array_slice($dominios_data, 0, 5) as $dominio): ?>
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-medium text-gray-700 truncate max-w-[200px]" title="<?= htmlspecialchars($dominio['dominio_nombre']) ?>">
                                    <?= htmlspecialchars($dominio['dominio_codigo']) ?>
                                </span>
                                <span class="text-gray-900 font-semibold"><?= number_format($dominio['porcentaje'], 0) ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="<?= $dominio['porcentaje'] >= 80 ? 'bg-green-500' : ($dominio['porcentaje'] >= 50 ? 'bg-amber-500' : 'bg-red-500') ?> h-2 rounded-full" 
                                     style="width: <?= $dominio['porcentaje'] ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($dominios_data) > 5): ?>
                        <div class="pt-2">
                            <a href="/controles" class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                                Ver todos los dominios (<?= count($dominios_data) ?>) →
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>
