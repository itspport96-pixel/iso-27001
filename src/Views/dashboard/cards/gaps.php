<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Análisis de Brechas</h3>
            </div>
            <a href="/gaps" class="text-sm font-medium text-primary-600 hover:text-primary-700">Ver todos →</a>
        </div>
    </div>
    
    <div class="px-6 py-5 space-y-4">
        
        <!-- Total GAPs -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Total GAPs activos</span>
            <span class="text-sm font-semibold text-gray-900"><?= $gaps['total'] ?></span>
        </div>

        <div class="border-t border-gray-200"></div>

        <!-- Por Prioridad -->
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Por Prioridad</p>
            
            <!-- Alta -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-sm font-medium text-gray-600">Alta</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <?= $gaps['prioridad_alta'] ?>
                </span>
            </div>

            <!-- Media -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                    <span class="text-sm font-medium text-gray-600">Media</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <?= $gaps['prioridad_media'] ?>
                </span>
            </div>

            <!-- Baja -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm font-medium text-gray-600">Baja</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <?= $gaps['prioridad_baja'] ?>
                </span>
            </div>
        </div>

        <div class="border-t border-gray-200"></div>

        <!-- Estado -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Completados</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <?= $gaps['completados'] ?>
            </span>
        </div>

        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Vencidos</span>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                <?= $gaps['vencidos'] ?>
            </span>
        </div>

    </div>

    <!-- Progress bar -->
    <div class="px-6 pb-5">
        <div class="mt-2">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">Avance promedio</span>
                <span class="font-semibold text-yellow-600"><?= $gaps['avance_promedio'] ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 h-3 rounded-full transition-all duration-500" style="width: <?= $gaps['avance_promedio'] ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Alerta si hay vencidos -->
    <?php if ($gaps['vencidos'] > 0): ?>
    <div class="bg-red-50 px-6 py-3 border-t border-red-100">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium text-red-800"><?= $gaps['vencidos'] ?> GAP(s) vencido(s)</span>
        </div>
    </div>
    <?php endif; ?>
</div>
