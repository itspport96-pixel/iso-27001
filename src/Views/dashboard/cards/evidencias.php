<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
    <div class="px-6 py-5 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                </div>
                <h3 class="ml-3 text-lg font-semibold text-gray-900">Evidencias y Documentación</h3>
            </div>
            <a href="/evidencias" class="text-sm font-medium text-primary-600 hover:text-primary-700">Ver todas →</a>
        </div>
    </div>
    
    <div class="px-6 py-5 space-y-4">
        
        <!-- Total evidencias -->
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Total evidencias</span>
            <span class="text-sm font-semibold text-gray-900"><?= $evidencias['total'] ?></span>
        </div>

        <div class="border-t border-gray-200"></div>

        <!-- Por Estado -->
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Por Estado</p>
            
            <!-- Aprobadas -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm font-medium text-gray-600">Aprobadas</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <?= $evidencias['aprobadas'] ?>
                </span>
            </div>

            <!-- Pendientes -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                    <span class="text-sm font-medium text-gray-600">Pendientes revisión</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <?= $evidencias['pendientes'] ?>
                </span>
            </div>

            <!-- Rechazadas -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-sm font-medium text-gray-600">Rechazadas</span>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <?= $evidencias['rechazadas'] ?>
                </span>
            </div>
        </div>

    </div>

    <!-- Progress bar -->
    <div class="px-6 pb-5">
        <div class="mt-2">
            <?php 
            $tasa_aprobacion = $evidencias['total'] > 0 
                ? round(($evidencias['aprobadas'] / $evidencias['total']) * 100) 
                : 0;
            ?>
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="font-medium text-gray-700">Tasa de aprobación</span>
                <span class="font-semibold text-purple-600"><?= $tasa_aprobacion ?>%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 h-3 rounded-full transition-all duration-500" style="width: <?= $tasa_aprobacion ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Info adicional -->
    <?php if ($evidencias['pendientes'] > 0): ?>
    <div class="bg-yellow-50 px-6 py-3 border-t border-yellow-100">
        <div class="flex items-center">
            <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-medium text-yellow-800"><?= $evidencias['pendientes'] ?> evidencia(s) pendiente(s) de revisión</span>
        </div>
    </div>
    <?php endif; ?>
</div>
