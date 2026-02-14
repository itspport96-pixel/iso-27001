<?php
// Datos para módulos accionables
$gaps_criticos = []; // Obtener GAPs críticos reales si tienes campo 'prioridad' o 'criticidad'
$evidencias_proximas = []; // Evidencias próximas a vencer
$controles_fallidos = []; // Controles no implementados o parciales

// Si no tienes métodos específicos, usar datos básicos
$gaps_recientes = $metricas['gaps']['total'] ?? 0;
$evidencias_pendientes = $metricas['evidencias']['pendientes'] ?? 0;
$controles_criticos = $metricas['controles']['no_implementados'] ?? 0;
?>

<div class="mt-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-6">Áreas que Requieren Atención</h2>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Módulo 1: GAPs Críticos -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">GAPs Críticos</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Alto Riesgo
                    </span>
                </div>
            </div>
            <div class="p-6">
                <?php if ($gaps_recientes == 0): ?>
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Sin GAPs críticos</p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="text-4xl font-bold text-red-600"><?= $gaps_recientes ?></div>
                        <p class="mt-1 text-sm text-gray-500">GAPs requieren atención</p>
                    </div>
                    <div class="mt-4">
                        <a href="/gaps" class="block w-full text-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-white hover:bg-red-50 transition">
                            Revisar GAPs →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Módulo 2: Controles sin Implementar -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-amber-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Controles Pendientes</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                        Acción Requerida
                    </span>
                </div>
            </div>
            <div class="p-6">
                <?php if ($controles_criticos == 0): ?>
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Todos los controles implementados</p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="text-4xl font-bold text-amber-600"><?= $controles_criticos ?></div>
                        <p class="mt-1 text-sm text-gray-500">Controles sin implementar</p>
                    </div>
                    <div class="mt-4">
                        <a href="/controles?estado=no_implementado" class="block w-full text-center px-4 py-2 border border-amber-300 rounded-lg text-sm font-medium text-amber-700 bg-white hover:bg-amber-50 transition">
                            Ver Controles →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Módulo 3: Evidencias Pendientes -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Evidencias Pendientes</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        Revisión
                    </span>
                </div>
            </div>
            <div class="p-6">
                <?php if ($evidencias_pendientes == 0): ?>
                    <div class="text-center py-6">
                        <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Sin evidencias pendientes</p>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="text-4xl font-bold text-blue-600"><?= $evidencias_pendientes ?></div>
                        <p class="mt-1 text-sm text-gray-500">Evidencias por validar</p>
                    </div>
                    <div class="mt-4">
                        <a href="/evidencias?estado=pendiente" class="block w-full text-center px-4 py-2 border border-blue-300 rounded-lg text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 transition">
                            Validar Evidencias →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>
