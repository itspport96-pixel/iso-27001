<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Requerimientos Obligatorios</h3>
            </div>
            <a href="/requerimientos" class="text-sm font-medium text-primary-600 hover:text-primary-700">Ver todos →</a>
        </div>
    </div>
    
    <div class="px-6 py-5 space-y-4">
        
        <!-- Total -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Total requerimientos</span>
            <span class="text-sm font-semibold text-gray-900"><?= $requerimientos['total'] ?></span>
        </div>

        <div class="border-t border-gray-200"></div>

        <!-- Pendientes -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                <span class="text-sm font-medium text-gray-600">Pendientes</span>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                <?= $requerimientos['pendientes'] ?>
            </span>
        </div>

        <!-- En proceso -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-2 h-2 bg-blue-400 rounded-full mr-2"></div>
                <span class="text-sm font-medium text-gray-600">En proceso</span>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                <?= $requerimientos['en_proceso'] ?>
            </span>
        </div>

        <!-- Completados -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                <span class="text-sm font-medium text-gray-600">Completados</span>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <?= $requerimientos['completados'] ?>
            </span>
        </div>

    </div>

    <!-- Progress bar -->
    <div class="px-6 pb-5">
        <div class="mt-2">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">Cumplimiento</span>
                <span class="font-semibold text-indigo-600"><?= $requerimientos['porcentaje'] ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-3 rounded-full transition-all duration-500" style="width: <?= $requerimientos['porcentaje'] ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Info adicional -->
    <?php if ($requerimientos['completados'] === $requerimientos['total']): ?>
    <div class="bg-green-50 px-6 py-3 border-t border-green-100">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium text-green-800">Todos los requerimientos completados</span>
        </div>
    </div>
    <?php endif; ?>
</div>
